<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request) {
        $currentDate = $request->query('date')
            ? \Carbon\Carbon::parse($request->query('date'))
            : now();

        $attendances = Attendance::where('work_date', $currentDate->toDateString())
            ->with('user', 'restTimes')
            ->get();

        return view('admin.attendance.list', compact('attendances', 'currentDate'));
    }

    public function show($id) {
        $attendance = Attendance::with('user', 'restTimes')->findOrFail($id);
        $restTimes = $attendance->restTimes;

        return view('admin.attendance.detail', compact('attendance', 'restTimes'));
    }

    public function update(Request $request, $id) {
        $attendance = Attendance::with('restTimes')->findOrFail($id);

        $attendance->update([
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
        ]);

        $attendance->restTimes()->update([
            'rest_start' => $request->input('rest_start'),
            'rest_end' => $attendance->restTimes->last()->rest_end,
        ])
    }
}
