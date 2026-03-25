<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now();
        $attendance = Attendance::where('user_id', auth()->user()->id)
                            ->where('work_date', $today->toDateString())
                            ->first();
        $status = $attendance?->status ?? '勤務外';

        return view('attendance.index', compact('status', 'today'));

    }

    public function store(Request $request)
    {
        $action = $request->input('action');
        $now = now();

        if ($action === 'clock_in') {
            Attendance::create([
                'user_id' => auth()->user()->id,
                'work_date' => $now->toDateString(),
                'clock_in' => $now->toTimeString(),
                'status' => '勤務中',
            ]);
        } elseif ($action === 'clock_out') {
            $attendance = Attendance::where('user_id', auth()->user()->id)
                ->where('work_date', now()->toDateString())
                ->first();

            $attendance->update([
                'clock_out' => now()->toTimeString(),
                'status' => '退勤済',
            ]);
        } elseif ($action === 'rest_start') {

            $attendance = Attendance::where('user_id', auth()->user()->id)
                ->where('work_date', $now->toDateString())
                ->first();

            $attendance->resttimes()->create([
                'rest_start' => $now->toTimeString(),
            ]);

            $attendance->update([
                'status' => '休憩中'
            ]);
        }elseif($action === 'rest_end'){

            $attendance = Attendance::where('user_id', auth()->user()->id)
                ->where('work_date', $now->toDateString())
                ->first();

            $restTime = $attendance->restTimes()
                ->whereNull('rest_end')
                ->first();

            $restTime->update([
                'rest_end' => $now->toTimeString(),
            ]);

            $attendance->update([
                'status' => '出勤中'
            ]);
        }

        return redirect('/attendance');
    }
}
