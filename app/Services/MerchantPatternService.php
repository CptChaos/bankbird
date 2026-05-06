<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class MerchantPatternService
{
    public function __construct(
        private readonly MerchantMapperService $mapper
    ) {}

    /**
     * Suggest initial patterns for a merchant based on its normalized_name.
     *
     * @return array<string>
     */
    public function suggestPatterns(string $normalizedName): array
    {
        $pattern = trim(mb_strtolower($normalizedName));

        return $pattern ? [$pattern] : [];
    }

    /**
     * Find the first merchant whose match_patterns contain a substring of the description.
     * Longer patterns take priority (more specific wins).
     */
    public function matchMerchant(string $rawDescription): ?Merchant
    {
        $normalized = mb_strtolower($this->mapper->normalize($rawDescription));

        return Merchant::whereNotNull('match_patterns')
            ->get()
            ->sortByDesc(fn (Merchant $m) => max(
                array_map('mb_strlen', $m->match_patterns ?? [''])
            ))
            ->first(function (Merchant $merchant) use ($normalized) {
                foreach ($merchant->match_patterns ?? [] as $pattern) {
                    if ($pattern !== '' && str_contains($normalized, mb_strtolower($pattern))) {
                        return true;
                    }
                }

                return false;
            });
    }

    /**
     * Apply all merchant patterns to transactions from a specific import.
     */
    public function syncImport(int $importId): int
    {
        $merchants = Merchant::whereNotNull('match_patterns')->get();

        if ($merchants->isEmpty()) {
            return 0;
        }

        $count = 0;

        Transaction::where('import_id', $importId)
            ->chunkById(200, function (Collection $transactions) use ($merchants, &$count) {
                foreach ($transactions as $transaction) {
                    $matched = $this->findMatch($transaction->raw_description, $merchants);

                    if ($matched && $transaction->merchant_id !== $matched->id) {
                        $transaction->update([
                            'merchant_id' => $matched->id,
                            'category_id' => $matched->category_id ?? $transaction->category_id,
                        ]);
                        $count++;
                    }
                }
            });

        return $count;
    }

    /**
     * Apply a single merchant's patterns to all transactions that don't have this merchant yet.
     */
    public function syncMerchant(Merchant $merchant): int
    {
        if (empty($merchant->match_patterns)) {
            return 0;
        }

        $count = 0;

        Transaction::chunkById(200, function (Collection $transactions) use ($merchant, &$count) {
            foreach ($transactions as $transaction) {
                $normalized = mb_strtolower($this->mapper->normalize($transaction->raw_description));

                foreach ($merchant->match_patterns as $pattern) {
                    if ($pattern !== '' && str_contains($normalized, mb_strtolower($pattern))) {
                        if ($transaction->merchant_id !== $merchant->id) {
                            $transaction->update([
                                'merchant_id' => $merchant->id,
                                'category_id' => $merchant->category_id ?? $transaction->category_id,
                            ]);
                            $count++;
                        }
                        break;
                    }
                }
            }
        });

        return $count;
    }

    /**
     * Apply all merchant patterns to all transactions (full retroactive sync).
     */
    public function syncAll(): int
    {
        $merchants = Merchant::whereNotNull('match_patterns')->get();

        if ($merchants->isEmpty()) {
            return 0;
        }

        $count = 0;

        Transaction::chunkById(200, function (Collection $transactions) use ($merchants, &$count) {
            foreach ($transactions as $transaction) {
                $matched = $this->findMatch($transaction->raw_description, $merchants);

                if ($matched && $transaction->merchant_id !== $matched->id) {
                    $transaction->update([
                        'merchant_id' => $matched->id,
                        'category_id' => $matched->category_id ?? $transaction->category_id,
                    ]);
                    $count++;
                }
            }
        });

        return $count;
    }

    private function findMatch(string $rawDescription, Collection $merchants): ?Merchant
    {
        $normalized = mb_strtolower($this->mapper->normalize($rawDescription));

        // Longer patterns win (more specific match first)
        $sorted = $merchants->sortByDesc(fn (Merchant $m) => max(
            array_map('mb_strlen', $m->match_patterns ?? [''])
        ));

        foreach ($sorted as $merchant) {
            foreach ($merchant->match_patterns ?? [] as $pattern) {
                if ($pattern !== '' && str_contains($normalized, mb_strtolower($pattern))) {
                    return $merchant;
                }
            }
        }

        return null;
    }
}
