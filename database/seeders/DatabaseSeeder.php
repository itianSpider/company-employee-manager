<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@grtech.com',
            'password' => 'password'
        ]);
        User::factory()->create([
            'name' => 'User',
            'email' => 'user@grtech.com',
            'password' => 'password'
        ]);
    }
}
