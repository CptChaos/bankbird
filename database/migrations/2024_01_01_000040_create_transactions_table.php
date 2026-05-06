<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('description');
            $table->string('raw_description');
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['debit', 'credit']);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('counterpart_iban', 34)->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->string('import_hash', 64)->unique();
            $table->timestamps();

            $table->index(['account_id', 'date']);
            $table->index(['category_id']);
            $table->index(['date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
