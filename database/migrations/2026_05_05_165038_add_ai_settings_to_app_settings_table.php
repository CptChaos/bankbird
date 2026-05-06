<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->boolean('ai_enabled')->default(false)->after('favicon_path');
            $table->string('ai_provider')->nullable()->after('ai_enabled');
            $table->text('claude_api_key')->nullable()->after('ai_provider');
            $table->text('openai_api_key')->nullable()->after('claude_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['ai_enabled', 'ai_provider', 'claude_api_key', 'openai_api_key']);
        });
    }
};
