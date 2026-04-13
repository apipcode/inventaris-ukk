<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Creaye akun Administrator   
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // create akun Staff Operator 
        User::factory()->create([
            'name' => 'Staff Operator',
            'email' => 'staff@staff.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
    }
}
