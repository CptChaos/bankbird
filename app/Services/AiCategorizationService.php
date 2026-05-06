<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Import;
use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCategorizationService
{
    private const BATCH_SIZE = 20;

    private const SYSTEM_PROMPT = <<<'PROMPT'
Je bent een financieel categoriseringsassistent. Retourneer UITSLUITEND een geldig JSON-array zonder uitleg, markdown of andere tekst.
Beschikbare categorieën: Boodschappen, Restaurant/Eten, Transport, Wonen, Abonnementen, Kleding, Gezondheid, Entertainment, Inkomen, Sparen, Overig.
Kies de meest passende categorie op basis van de omschrijving. Bij inkomende betalingen (type: credit) kies Inkomen tenzij duidelijk iets anders.
JSON-formaat: [{"transaction_id":1,"category":"Boodschappen","subcategory":null,"merchant":"Albert Heijn"}]
PROMPT;

    public function __construct(
        private readonly MerchantMapperService $merchantMapper
    ) {}

    public function isEnabled(): bool
    {
        return (bool) AppSetting::current()->ai_enabled;
    }

    /**
     * Categorize all uncategorized transactions in a completed import.
     * First tries merchant pattern matching, then falls back to AI if enabled.
     */
    public function categorizeImport(Import $import): void
    {
        $transactions = Transaction::where('import_id', $import->id)
            ->whereNull('category_id')
            ->whereNull('merchant_id')
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        $normalized = $transactions->mapWithKeys(fn ($t) => [
            $t->id => $this->merchantMapper->normalize($t->raw_description),
        ]);

        $merchantByNormalized = Merchant::whereIn('normalized_name', $normalized->values()->unique()->filter()->all())
            ->whereNotNull('category_id')
            ->get()
            ->keyBy('normalized_name');

        $withoutMapping = collect();

        foreach ($transactions as $transaction) {
            $merchant = $merchantByNormalized->get($normalized[$transaction->id]);

            if ($merchant) {
                $transaction->update([
                    'merchant_id' => $merchant->id,
                    'category_id' => $merchant->category_id,
                ]);
            } else {
                $withoutMapping->push($transaction);
            }
        }

        if ($withoutMapping->isNotEmpty() && $this->isEnabled()) {
            $this->categorizeTransactions($withoutMapping);
        }
    }

    /**
     * Categorize a collection of transactions using the configured AI provider.
     *
     * @return array<int, array<string, mixed>>
     */
    public function categorizeTransactions(Collection $transactions): array
    {
        $this->assertEnabled();

        $allResults = [];

        foreach ($transactions->chunk(self::BATCH_SIZE) as $chunk) {
            $data = $chunk->map(fn (Transaction $t) => [
                'transaction_id' => $t->id,
                'description'    => $t->description,
                'amount'         => (float) $t->amount,
                'type'           => $t->type->value,
            ])->values()->toArray();

            try {
                $results    = $this->categorizeChunk($data);
                $this->merchantMapper->applyCategorizationResults($results, $chunk);
                $allResults = array_merge($allResults, $results);
            } catch (\Exception $e) {
                Log::error('AiCategorizationService: chunk failed', [
                    'error'           => $e->getMessage(),
                    'transaction_ids' => array_column($data, 'transaction_id'),
                ]);

                throw $e;
            }
        }

        return $allResults;
    }

    private function assertEnabled(): void
    {
        if (! $this->isEnabled()) {
            throw new \RuntimeException(
                'AI categorisatie is uitgeschakeld. Schakel het in via Instellingen → AI Categorisatie.'
            );
        }
    }

    private function getApiKey(): string
    {
        $setting  = AppSetting::current();
        $provider = $setting->ai_provider ?? 'claude';

        $key = $provider === 'openai'
            ? $setting->openai_api_key
            : ($setting->claude_api_key ?: config('anthropic.api_key'));

        if (! $key) {
            $name = $provider === 'openai' ? 'OpenAI' : 'Claude (Anthropic)';
            throw new \RuntimeException(
                "Geen {$name} API-sleutel geconfigureerd. Voer deze in via Instellingen → AI Categorisatie."
            );
        }

        return $key;
    }

    private function categorizeChunk(array $transactionData): array
    {
        $setting  = AppSetting::current();
        $provider = $setting->ai_provider ?? 'claude';

        return $provider === 'openai'
            ? $this->categorizeWithOpenAi($transactionData)
            : $this->categorizeWithClaude($transactionData);
    }

    private function categorizeWithClaude(array $transactionData): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->getApiKey(),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])
            ->timeout((int) config('anthropic.timeout', 60))
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => config('anthropic.model', 'claude-haiku-4-5-20251001'),
                'max_tokens' => 2048,
                'system'     => self::SYSTEM_PROMPT,
                'messages'   => [
                    ['role' => 'user', 'content' => json_encode($transactionData, JSON_UNESCAPED_UNICODE)],
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Claude API-fout {$response->status()}: " . $response->body()
            );
        }

        return $this->parseResponse($response->json('content.0.text') ?? '');
    }

    private function categorizeWithOpenAi(array $transactionData): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getApiKey(),
            'Content-Type'  => 'application/json',
        ])
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => 'gpt-4o-mini',
                'max_tokens' => 2048,
                'messages'   => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user', 'content' => json_encode($transactionData, JSON_UNESCAPED_UNICODE)],
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "OpenAI API-fout {$response->status()}: " . $response->body()
            );
        }

        return $this->parseResponse($response->json('choices.0.message.content') ?? '');
    }

    private function parseResponse(string $content): array
    {
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/m', '', $content) ?? $content;
        $content = trim($content);

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Kon AI-antwoord niet verwerken als JSON: ' . json_last_error_msg());
        }

        return is_array($decoded) ? $decoded : [];
    }
}
