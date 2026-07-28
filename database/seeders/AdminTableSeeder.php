<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('adminuser')->insert([
            [
                'Username' => 'Admin User',
                'PasswordHash' => Hash::make('Admin@123'), // Replace with secure password
                'Email' => 'admin@gmail.com',
                'CreatedAt' => now(),
                'UpdatedAt' => now(),
            ],
        ]);
    }
}
