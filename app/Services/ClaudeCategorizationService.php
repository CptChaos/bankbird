<?php

namespace App\Services;

use App\Models\Import;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeCategorizationService
{
    private const BATCH_SIZE = 20;
    private const API_URL    = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    private const SYSTEM_PROMPT = <<<'PROMPT'
Je bent een financieel categoriseringsassistent. Retourneer UITSLUITEND een geldig JSON-array zonder uitleg, markdown of andere tekst.
Beschikbare categorieën: Boodschappen, Restaurant/Eten, Transport, Wonen, Abonnementen, Kleding, Gezondheid, Entertainment, Inkomen, Sparen, Overig.
Kies de meest passende categorie op basis van de omschrijving. Bij inkomende betalingen (type: credit) kies Inkomen tenzij duidelijk iets anders.
JSON-formaat: [{"transaction_id":1,"category":"Boodschappen","subcategory":null,"merchant":"Albert Heijn"}]
PROMPT;

    public function __construct(
        private readonly MerchantMapperService $merchantMapper
    ) {}

    public function categorizeImport(Import $import): void
    {
        $transactions = Transaction::where('import_id', $import->id)
            ->whereNull('category_id')
            ->whereNull('merchant_id')
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        // Pre-compute normalized names once and bulk-fetch matching merchants
        $normalized = $transactions->mapWithKeys(fn ($t) => [
            $t->id => $this->merchantMapper->normalize($t->raw_description),
        ]);

        $merchantByNormalized = \App\Models\Merchant::whereIn('normalized_name', $normalized->values()->unique()->filter()->all())
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

        if ($withoutMapping->isNotEmpty()) {
            $this->categorizeTransactions($withoutMapping);
        }
    }

    public function categorizeTransactions(Collection $transactions): array
    {
        $allResults = [];

        foreach ($transactions->chunk(self::BATCH_SIZE) as $chunk) {
            $data = $chunk->map(fn (Transaction $t) => [
                'transaction_id' => $t->id,
                'description'    => $t->description,
                'amount'         => (float) $t->amount,
                'type'           => $t->type->value,
            ])->values()->toArray();

            try {
                $results = $this->categorizeChunk($data);
                $this->merchantMapper->applyCategorizationResults($results, $chunk);
                $allResults = array_merge($allResults, $results);
            } catch (\Exception $e) {
                Log::error('ClaudeCategorizationService: chunk failed', [
                    'error'         => $e->getMessage(),
                    'transaction_ids' => array_column($data, 'transaction_id'),
                ]);
            }
        }

        return $allResults;
    }

    protected function categorizeChunk(array $transactionData): array
    {
        $prompt = $this->buildPrompt($transactionData);

        $response = Http::withHeaders([
            'x-api-key'         => config('anthropic.api_key'),
            'anthropic-version' => self::API_VERSION,
            'content-type'      => 'application/json',
        ])
        ->timeout((int) config('anthropic.timeout', 60))
        ->post(self::API_URL, [
            'model'      => config('anthropic.model'),
            'max_tokens' => 2048,
            'system'     => $prompt['system'],
            'messages'   => [
                ['role' => 'user', 'content' => $prompt['user']],
            ],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Claude API error {$response->status()}: " . $response->body()
            );
        }

        $content = $response->json('content.0.text') ?? '';

        return $this->parseResponse($content);
    }

    protected function buildPrompt(array $transactionData): array
    {
        return [
            'system' => self::SYSTEM_PROMPT,
            'user'   => json_encode($transactionData, JSON_UNESCAPED_UNICODE),
        ];
    }

    protected function parseResponse(string $content): array
    {
        // Strip markdown code fences if present
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/m', '', $content) ?? $content;
        $content = trim($content);

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Could not parse Claude response as JSON: ' . json_last_error_msg());
        }

        return is_array($decoded) ? $decoded : [];
    }
}
