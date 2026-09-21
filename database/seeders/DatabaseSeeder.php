<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
<<<<<<< HEAD
            'name' => 'Test Security User',
            'email' => 'admin@securets.com',
            'password' => bcrypt('password123'),
=======
            'name' => 'Test User',
            'email' => 'test@example.com',
>>>>>>> 8c26416e63b8be823a14e3ab1cc64fa61a4d31d7
        ]);
    }
}
