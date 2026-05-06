<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class MerchantMapperService
{
    public function normalize(string $description): string
    {
        $normalized = mb_strtolower($description);

        // Strip ING payment terminal codes
        $normalized = preg_replace('/\bbet(?:aalpas|aalautomaat)?\s*nr[:\.]?\s*[\w\d]+/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\bbea\s*nr[:\.]?\s*[\w\d]+/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\bpas\s*\d+/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\bnr[:\.]?\s*[\w\d]{4,}/i', '', $normalized) ?? $normalized;

        // Strip dates and times (DD-MM-YYYY HH:MM)
        $normalized = preg_replace('/\d{2}-\d{2}-\d{4}(?:\s+\d{2}:\d{2})?/', '', $normalized) ?? $normalized;

        // Strip location codes, card numbers, etc.
        $normalized = preg_replace('/\b[A-Z]{2,}\d{4,}\b/', '', $normalized) ?? $normalized;

        // Normalize whitespace and strip leading/trailing
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        // Limit to first 60 chars to capture the merchant name
        $normalized = mb_substr($normalized, 0, 60);
        $normalized = trim($normalized);

        return $normalized;
    }

    public function findOrCreate(string $rawDescription, ?string $categoryName = null, ?string $displayName = null): Merchant
    {
        $normalized = $this->normalize($rawDescription);

        $merchant = Merchant::where('normalized_name', $normalized)->first();

        if ($merchant) {
            if ($categoryName && ! $merchant->category_id) {
                $category = Category::where('name', $categoryName)->first();
                if ($category) {
                    $merchant->update(['category_id' => $category->id]);
                }
            }

            return $merchant;
        }

        $category = $categoryName ? Category::where('name', $categoryName)->first() : null;

        return Merchant::create([
            'name'            => $displayName ?? ucwords($normalized),
            'normalized_name' => $normalized,
            'category_id'     => $category?->id,
        ]);
    }

    public function applyCategorizationResults(array $results, Collection $transactions): void
    {
        $transactionMap = $transactions->keyBy('id');

        foreach ($results as $result) {
            $transactionId = $result['transaction_id'] ?? null;
            if (! $transactionId) {
                continue;
            }

            $transaction = $transactionMap->get($transactionId);
            if (! $transaction) {
                continue;
            }

            $categoryName  = $result['category'] ?? null;
            $merchantName  = $result['merchant'] ?? null;

            $category = $categoryName ? Category::where('name', $categoryName)->first() : null;
            $merchant = $merchantName
                ? $this->findOrCreate($transaction->raw_description, $categoryName, $merchantName)
                : null;

            $transaction->update([
                'category_id' => $category?->id ?? $transaction->category_id,
                'merchant_id' => $merchant?->id ?? $transaction->merchant_id,
            ]);
        }
    }
}
