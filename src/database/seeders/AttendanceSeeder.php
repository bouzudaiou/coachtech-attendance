<?php

namespace Database\Seeders;

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
                'work_date' => '2026-03-10',
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'status' => '退勤済',
            ],
            [
                'user_id' => 2,
                'work_date' => '2026-03-15',
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'status' => '退勤済',
            ],
            [
                'user_id' => 1,
                'work_date' => '2026-04-01',
                'clock_in' => '08:30',
                'status' => '出勤中',
            ],
            [
                'user_id' => 2,
                'work_date' => '2026-04-01',
                'clock_in' => '08:30',
                'status' => '休憩中',
            ],
            [
                'user_id' => 1,
                'work_date' => '2026-05-10',
                'status' => '勤務外',
            ],
            [
                'user_id' => 2,
                'work_date' => '2026-05-10',
                'status' => '勤務外',
            ],
        ];
        foreach ($attendances as $attendance) {
            Attendance::create($attendance);
        }
    }
}
