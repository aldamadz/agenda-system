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
    // Buat Manager
    $manager = \App\Models\User::factory()->create([
        'name' => 'Manager',
        'email' => 'manager@test.com',
        'password' => bcrypt('marison123'),
    ]);

    // Buat Staff dan sambungkan ke Manager
    \App\Models\User::factory()->create([
        'name' => 'Staff Aldama',
        'email' => 'staff@test.com',
        'parent_id' => $manager->id,
        'password' => bcrypt('marison123'),
    ]);
}
}
