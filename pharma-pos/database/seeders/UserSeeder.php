<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@pharmapos.com',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@pharmapos.com',
                'password' => Hash::make('password'),
                'role_id' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Kasir',
                'email' => 'kasir@pharmapos.com',
                'password' => Hash::make('password'),
                'role_id' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
