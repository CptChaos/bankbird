<?php

namespace App\Services;

use App\Enums\ImportStatus;
use App\Models\Account;
use App\Models\Import;
use App\Models\Transaction;
use App\Services\MerchantPatternService;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class PdfImportService
{
    public function handle(UploadedFile $file, Account $account, ?string $originalFilename = null): Import
    {
        // smalot/pdfparser is memory-hungry on multi-page bank statements
        @ini_set('memory_limit', '512M');

        $displayName = $originalFilename ?? $file->getClientOriginalName();
        $storedName  = Str::uuid()->toString() . '-' . preg_replace('/[^A-Za-z0-9._-]/', '_', $displayName);
        $path        = $file->storeAs('imports', $storedName, 'local');

        $import = Import::create([
            'account_id' => $account->id,
            'filename'   => $displayName,
            'status'     => ImportStatus::Pending,
        ]);

        $this->processImport($import, storage_path('app/' . $path));

        return $import->fresh();
    }

    public function processImport(Import $import, string $filePath): void
    {
        $import->update(['status' => ImportStatus::Processing]);

        try {
            $rows = $this->parsePdf($filePath);

            $total      = 0;
            $newCount   = 0;
            $duplicates = 0;

            foreach ($rows as $row) {
                $total++;
                $hash = $this->makeImportHash($row, $import->account_id);

                try {
                    Transaction::create([
                        'account_id'       => $import->account_id,
                        'import_id'        => $import->id,
                        'date'             => $row['date'],
                        'description'      => $row['description'],
                        'raw_description'  => $row['description'],
                        'amount'           => $row['amount'],
                        'type'             => $row['type'],
                        'counterpart_iban' => $row['counterpart_iban'] ?? null,
                        'imported_at'      => now(),
                        'import_hash'      => $hash,
                    ]);
                    $newCount++;
                } catch (UniqueConstraintViolationException) {
                    $duplicates++;
                } catch (\Exception $e) {
                    Log::warning('PdfImportService: skipping row', ['error' => $e->getMessage(), 'row' => $row]);
                    $total--;
                }
            }

            $import->update([
                'status'     => ImportStatus::Completed,
                'total'      => $total,
                'new'        => $newCount,
                'duplicates' => $duplicates,
            ]);

            Account::recalculateBalance($import->account_id);

            // Auto-match transactions against existing merchant patterns
            app(MerchantPatternService::class)->syncImport($import->id);
        } catch (\Exception $e) {
            $import->update([
                'status'        => ImportStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function detectAccountIban(string $filePath): ?string
    {
        @ini_set('memory_limit', '512M');

        try {
            $parser = new Parser();
            $text   = $parser->parseFile($filePath)->getText();

            // ING uses two account-identifier formats:
            //  - NL IBAN ("NL59 INGB 0007 8651 73") for current accounts
            //  - D-number ("D 775-02488") for linked savings accounts
            // ING PDFs sometimes use non-breaking spaces (U+00A0); the /u flag handles that.
            $ibanPattern = '(NL\d{2}\s?[A-Z]{4}\s?\d{4}\s?\d{4}\s?\d{2})';
            $dNumPattern = '(D\s?\d{3}[\s\-]?\d{5})';
            $idPattern   = "/({$ibanPattern}|{$dNumPattern})/iu";

            // Preferred: the value right after the "Rekeningnummer" label is THIS account.
            // We must NOT pick up the "Gekoppelde betaalrekening" IBAN, which appears further
            // down on savings statements and is the parent checking account.
            // Use a regex that matches "Rekeningnummer" but not "Gekoppelde betaalrekening".
            if (preg_match('/(?<!gekoppelde[\s\xC2\xA0]betaal)rekeningnummer/iu', $text, $labelMatch, PREG_OFFSET_CAPTURE)) {
                $after = mb_substr($text, $labelMatch[0][1] + mb_strlen($labelMatch[0][0]), 200);
                if (preg_match($idPattern, $after, $m)) {
                    return $this->normalizeIban($m[1]);
                }
            }

            // Fallback: first IBAN or D-number in the document header.
            if (preg_match($idPattern, mb_substr($text, 0, 1500), $m)) {
                return $this->normalizeIban($m[1]);
            }
        } catch (\Exception) {
            //
        }

        return null;
    }

    private function normalizeIban(string $raw): string
    {
        // Strip whitespace, non-breaking spaces, and dashes — works for both NL IBANs and D-numbers.
        return strtoupper(preg_replace('/[\s\-]+/u', '', $raw));
    }

    protected function parsePdf(string $filePath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($filePath);
        $text   = $pdf->getText();

        return $this->parseIngText($text);
    }

    protected function parseIngText(string $text): array
    {
        $lines        = explode("\n", $text);
        $transactions = [];
        $pending      = null; // multi-line tx waiting for type+amount completion

        // Single-line ING current-account / new credit-card format:
        //   DD-MM-YYYY<merchant><TAB><type><TAB><+/-><amount>
        // Older credit-card statements use a space between the sign and the digits ("- 1,11"),
        // hence \s* between [+-] and [\d.,].
        $singleLinePattern = '/^(\d{2}-\d{2}-\d{4})(.+?)\t(.+?)\t([+-])\s*([\d.,]+)\s*$/';

        // Multi-line ING savings format spans 3 lines:
        //   line A: DD-MM-YYYY<description>          (no tab)
        //   line B: <counterpart-iban>               (optional)
        //   line C: <type-keyword><TAB><+/-><amount>
        $dateLineOnlyPattern = '/^(\d{2}-\d{2}-\d{4})(.+)$/';
        $typeAmountPattern   = '/^(.+?)\t([+-])\s*([\d.,]+)\s*$/';

        $ibanLabelPattern = '/IBAN:\s*(NL\d{2}[A-Z]{4}\d{10})/';
        $bareIbanPattern  = '/^(NL\d{2}[A-Z]{4}\d{10})\s*$/';

        $finalize = function (array $row) use (&$transactions): void {
            // Skip incomplete rows — e.g. the "Periode 01-01-2025 t/m 31-12-2025" header line
            // matches the date-prefix shape but never gets type+amount completion.
            if (empty($row['type']) || ! is_numeric($row['amount'] ?? null)) {
                return;
            }

            try {
                $row['date'] = Carbon::createFromFormat('d-m-Y', $row['date_raw'])->toDateString();
            } catch (\Exception) {
                return;
            }
            unset($row['date_raw']);
            $transactions[] = $row;
        };

        foreach ($lines as $line) {
            $line = rtrim($line);
            if ($line === '') {
                continue;
            }

            // 1) Full single-line transaction (current accounts).
            if (preg_match($singleLinePattern, $line, $m)) {
                if ($pending !== null) {
                    $finalize($pending);
                    $pending = null;
                }

                $finalize([
                    'date_raw'         => $m[1],
                    'description'      => trim($m[2]),
                    'counterpart_iban' => null,
                    'type'             => $m[4] === '+' ? 'credit' : 'debit',
                    'amount'           => $this->parseAmount($m[5]),
                ]);
                continue;
            }

            // 2) Date-prefixed description (start of multi-line savings tx, no tab).
            if (! str_contains($line, "\t") && preg_match($dateLineOnlyPattern, $line, $m)) {
                if ($pending !== null) {
                    $finalize($pending);
                }

                $pending = [
                    'date_raw'         => $m[1],
                    'description'      => trim($m[2]),
                    'counterpart_iban' => null,
                    'type'             => null,
                    'amount'           => null,
                ];
                continue;
            }

            // 3) Bare IBAN line — counterpart IBAN for the multi-line pending tx.
            if ($pending !== null && preg_match($bareIbanPattern, $line, $m)) {
                $pending['counterpart_iban'] = $m[1];
                continue;
            }

            // 4) Type+amount line that closes the pending multi-line transaction.
            if ($pending !== null && preg_match($typeAmountPattern, $line, $m)) {
                $pending['description'] = trim($pending['description'] . ' (' . trim($m[1]) . ')');
                $pending['type']        = $m[2] === '+' ? 'credit' : 'debit';
                $pending['amount']      = $this->parseAmount($m[3]);
                $finalize($pending);
                $pending = null;
                continue;
            }

            // 5) "IBAN: NL..." metadata line for the most recent single-line transaction.
            if (preg_match($ibanLabelPattern, $line, $m)) {
                $idx = array_key_last($transactions);
                if ($idx !== null && $transactions[$idx]['counterpart_iban'] === null) {
                    $transactions[$idx]['counterpart_iban'] = $m[1];
                }
            }
        }

        if ($pending !== null && $pending['type'] !== null) {
            $finalize($pending);
        }

        return $transactions;
    }

    protected function parseAmount(string $raw): float
    {
        // Dutch format: 1.234,56 → 1234.56
        $cleaned = str_replace(['.', ' '], '', $raw);
        $cleaned = str_replace(',', '.', $cleaned);

        return (float) $cleaned;
    }

    protected function makeImportHash(array $row, int $accountId): string
    {
        return hash('sha256', implode('|', [
            $row['date'],
            $row['description'],
            number_format((float) $row['amount'], 2, '.', ''),
            $row['type'],
            $accountId,
        ]));
    }
}
