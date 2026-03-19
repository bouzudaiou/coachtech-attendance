<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stampCorrectionRequests = [
         [
            'attendance_id' => 1,
             'user_id' => 1,
             'clock_in' => '09:30',
             'clock_out' => '18:30',
             'remarks' => '業務のため',
             'status' => '承認済み',
         ],
         [
             'attendance_id' => 2,
             'user_id' => 2,
             'clock_in' => '07:30',
             'clock_out' => '16:30',
             'remarks' => '業務のため',
             'status' => '承認待ち',
         ],
        ];

        foreach ($stampCorrectionRequests as $request) {
            StampCorrectionRequest::create($request);
        }
    }
}
