<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserLogin;
use Illuminate\Support\Facades\Hash;

class UserLoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserLogin::create([
            'username' => 'admin',
            'password' => 'password', // Will be hashed automatically by the model
            'remarks' => 'Default admin user',
            'created_by' => 'system',
        ]);
    }
}
