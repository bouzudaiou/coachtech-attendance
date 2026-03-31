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
}
