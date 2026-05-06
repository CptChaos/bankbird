<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->after('account_id')
                ->constrained('imports')->nullOnDelete();
            $table->index('import_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['import_id']);
            $table->dropIndex(['import_id']);
            $table->dropColumn('import_id');
        });
    }
};
