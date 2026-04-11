<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StampCorrectionRequest;
use App\Models\User;

class StampCorrectionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taroId = User::where('name', 'taro')->value('id');
        $jiroId = User::where('name', 'jiro')->value('id');

        $stampCorrectionRequests = [
            [
                'attendance_id' => 1,
                'user_id' => $taroId,  // ← 動的取得
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'remarks' => '業務のため',
                'status' => '承認済み',
            ],
            [
                'attendance_id' => 2,
                'user_id' => $jiroId,  // ← taroかjiroか仕様に合わせて
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
