<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RestTime;

class RestTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restTimes = [
            [
                'attendance_id' => 1,
                'rest_start' => '11:30',
                'rest_end' => '12:00',
            ],
            [
                'attendance_id' => 1,
                'rest_start' => '15:00',
                'rest_end' => '15:30',
            ],
            [
                'attendance_id' => 2,
                'rest_start' => '12:30',
                'rest_end' => '13:30',
            ],
        ];
        foreach ($restTimes as $restTime) {
            RestTime::create($restTime);
        }
    }
}
