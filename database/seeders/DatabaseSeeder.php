<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            MerchantSeeder::class,
        ]);

        if (config('app.demo_mode')) {
            $this->call(DemoSeeder::class);
        }
    }
}
