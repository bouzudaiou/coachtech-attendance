<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'taro',
                'email' => 'taro@example.com',
                'password' => 'password1234',
                'role' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'jiro',
                'email' => 'jiro@example.com',
                'password' => 'password5678',
                'role' => 'user',
                'email_verified_at' => now(),
            ],
        ];
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
