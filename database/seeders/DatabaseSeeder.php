<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the demo graph, which is the only
     * thing there is to seed: an empty application shows a reviewer nothing.
     */
    public function run(): void
    {
        $this->call(DemoSeeder::class);
    }
}
