<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Ejecutar las seeds RoleSeeder, InitialSeeder, RubricaSeeder
        $this->call([
            RoleSeeder::class,
            InitialSeeder::class,
            RubricaSeeder::class,
        ]);
    }
}
