<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen columns to hold encrypted values (~200+ chars)
        Schema::table('accounts', function (Blueprint $table) {
            $table->text('iban')->nullable()->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->text('counterpart_iban')->nullable()->change();
        });

        // Encrypt existing plain-text IBANs
        DB::table('accounts')->whereNotNull('iban')->get()->each(function ($row) {
            DB::table('accounts')
                ->where('id', $row->id)
                ->update(['iban' => Crypt::encrypt($row->iban)]);
        });

        DB::table('transactions')->whereNotNull('counterpart_iban')->get()->each(function ($row) {
            DB::table('transactions')
                ->where('id', $row->id)
                ->update(['counterpart_iban' => Crypt::encrypt($row->counterpart_iban)]);
        });
    }

    public function down(): void
    {
        // Decrypt back to plain text
        DB::table('accounts')->whereNotNull('iban')->get()->each(function ($row) {
            try {
                DB::table('accounts')
                    ->where('id', $row->id)
                    ->update(['iban' => Crypt::decrypt($row->iban)]);
            } catch (\Exception) {
                // Already plain text
            }
        });

        DB::table('transactions')->whereNotNull('counterpart_iban')->get()->each(function ($row) {
            try {
                DB::table('transactions')
                    ->where('id', $row->id)
                    ->update(['counterpart_iban' => Crypt::decrypt($row->counterpart_iban)]);
            } catch (\Exception) {
                // Already plain text
            }
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('iban', 34)->nullable()->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('counterpart_iban', 34)->nullable()->change();
        });
    }
};
