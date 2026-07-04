<?php

namespace Database\Seeders;

use App\Models\UserLogin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserLoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserLogin::updateOrCreate(
            ['username' => 'DevanshIT'],
            [
                'password' => Hash::make('Dev@123r45'),
                'remarks' => 'Seeded user',
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]
        );
    }
}
