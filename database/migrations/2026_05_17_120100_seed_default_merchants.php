<?php

use Database\Seeders\MerchantSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MerchantSeeder::sync();
    }

    public function down(): void
    {
        // No-op: standaard merchants worden niet automatisch verwijderd om
        // gekoppelde transacties intact te houden.
    }
};
