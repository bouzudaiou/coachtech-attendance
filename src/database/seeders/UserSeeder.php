<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'password' => bcrypt('password1234'),
                'role' => 'user',
            ],
            [
                'name' => 'jiro',
                'email' => 'jiro@example.com',
                'password' => bcrypt('password5678'),
                'role' => 'user',
            ],
            [
                'name' => 'saburo',
                'email' => 'saburo@example.com',
                'password' => bcrypt('password9876'),
                'role' => 'user',
            ],
            [
                'name' => 'siro',
                'email' => 'siro@example.com',
                'password' => bcrypt('password5432'),
                'role' => 'user',
            ],
        ];
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
