<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Re-encrypt IBAN values from the old format (Crypt::encrypt with serialization)
 * to the new format used by Laravel 11's `encrypted` cast (without serialization).
 *
 * Old: Crypt::encrypt($value)          → decrypt gives s:18:"NL59...";
 * New: Crypt::encrypt($value, false)   → decrypt gives the raw IBAN string
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('accounts')
            ->whereNotNull('iban')
            ->orderBy('id')
            ->each(function (object $account): void {
                try {
                    // Old format: encrypted WITH serialization — decrypt with unserialize=true
                    $plain = Crypt::decrypt($account->iban, true);
                } catch (Throwable) {
                    // Already in new format or unreadable — skip
                    return;
                }

                if (is_string($plain) && ! empty($plain)) {
                    // Re-encrypt WITHOUT serialization (new Laravel 11 encrypted cast format)
                    DB::table('accounts')
                        ->where('id', $account->id)
                        ->update(['iban' => Crypt::encrypt($plain, false)]);
                }
            });
    }

    public function down(): void
    {
        // Not reversible — re-encrypting back to the old format is not needed
    }
};
