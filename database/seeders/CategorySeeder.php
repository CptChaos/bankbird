<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Boodschappen',     'icon' => 'shopping-cart',       'color' => '#22c55e'],
            ['name' => 'Restaurant/Eten',  'icon' => 'fire',                'color' => '#f97316'],
            ['name' => 'Transport',        'icon' => 'truck',               'color' => '#3b82f6'],
            ['name' => 'Wonen',            'icon' => 'home',                'color' => '#a855f7'],
            ['name' => 'Abonnementen',     'icon' => 'device-phone-mobile', 'color' => '#06b6d4'],
            ['name' => 'Kleding',          'icon' => 'scissors',            'color' => '#ec4899'],
            ['name' => 'Gezondheid',       'icon' => 'heart',               'color' => '#ef4444'],
            ['name' => 'Entertainment',    'icon' => 'musical-note',        'color' => '#8b5cf6'],
            ['name' => 'Inkomen',          'icon' => 'arrow-trending-up',   'color' => '#10b981'],
            ['name' => 'Sparen',           'icon' => 'archive-box',         'color' => '#14b8a6'],
            ['name' => 'Overig',           'icon' => 'ellipsis-horizontal', 'color' => '#94a3b8'],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(
                ['name' => $data['name'], 'parent_id' => null],
                array_merge($data, ['is_system' => true])
            );
        }
    }
}
