<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendances = [
            [
                'user_id' => 1,
                'work_date' => now()->subMonth()->format('Y-m-d'),
                'status' =>'勤務外',
            ],
            [
                'user_id' => 1,
                
            ]
            [
                'user_id' => 2,
                'work_date' => now()->format('Y-m-d'),
                'clock_in' => '08:30',
                'status' => '出勤中',
            ],
            [
                'user_id' => 3,
                'work_date' => now()->addMonth()->format('Y-m-d'),
                'clock_in' => '08:30',
                'status' => '休憩中',
            ],
            [
                'user_id' => 4,
                'work_date' => now()->format('Y-m-d'),
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'status' => '退勤済み'
            ],
        ];
        foreach ($attendances as $attendance) {
            Attendance::create($attendance);
        }
    }
}
