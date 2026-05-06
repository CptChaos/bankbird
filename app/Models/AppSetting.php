<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'logo_path',
        'logo_height',
        'favicon_path',
        'ai_enabled',
        'ai_provider',
        'claude_api_key',
        'openai_api_key',
    ];

    protected function casts(): array
    {
        return [
            'ai_enabled'     => 'boolean',
            'claude_api_key' => 'encrypted',
            'openai_api_key' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([]);
    }
}
