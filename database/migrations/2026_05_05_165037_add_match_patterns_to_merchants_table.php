<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->json('match_patterns')->nullable()->after('logo_url');
        });

        // Backfill existing merchants with their normalized_name as initial pattern
        $merchants = DB::table('merchants')->whereNotNull('normalized_name')->get();
        foreach ($merchants as $merchant) {
            $pattern = trim(mb_strtolower($merchant->normalized_name));
            if ($pattern) {
                DB::table('merchants')
                    ->where('id', $merchant->id)
                    ->update(['match_patterns' => json_encode([$pattern])]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn('match_patterns');
        });
    }
};
