<?php

namespace Database\Seeders;

use App\Models\StaffUser;
use App\Models\UserLogin;
use Illuminate\Database\Seeder;

class UserLoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAtulGautam();
        $this->seedDevanshIt();
    }

    private function seedAtulGautam(): void
    {
        $staff = StaffUser::updateOrCreate(
            ['username' => 'Atul.Gautam'],
            [
                'email' => 'atul.gautam@rinfinite.com',
                'phone_number' => null,
                'date_of_birth' => null,
                'remarks' => 'Seeded admin user',
            ]
        );

        UserLogin::updateOrCreate(
            ['username' => 'AtulGautam'],
            [
                'staff_user_id' => $staff->id,
                'password' => 'Atul.G@RADiiX',
                'remarks' => 'Seeded login — production admin',
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]
        );
    }

    private function seedDevanshIt(): void
    {
        $staff = StaffUser::updateOrCreate(
            ['username' => 'Devansh.IT'],
            [
                'email' => null,
                'remarks' => 'Seeded developer account',
            ]
        );

        UserLogin::updateOrCreate(
            ['username' => 'DevanshIT'],
            [
                'staff_user_id' => $staff->id,
                'password' => 'Dev@123r45',
                'remarks' => 'Seeded user',
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]
        );
    }
}
