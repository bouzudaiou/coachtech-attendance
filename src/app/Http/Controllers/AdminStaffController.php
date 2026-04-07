<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->get();

        return view('admin.staff.list', compact('users'));
    }

    public function show(Request $request, $id)
    {

        $user = User::findOrFail($id);

        $currentMonth = $request->query('month')
            ? \Carbon\Carbon::parse($request->query('month'))
            : now();

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->with('restTimes')
            ->get();

        $attendanceMap = $attendances->keyBy(function ($a) {
            return $a->work_date;
        });

        $days = \Carbon\CarbonPeriod::create(
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        );

        return view('admin.attendance.staff', compact('user', 'attendances', 'attendanceMap', 'days', 'currentMonth'));
    }

    public function export(Request $request, $id)
    {
        User::findOrFail($id);

        $currentMonth = $request->query('month')
            ? \Carbon\Carbon::parse($request->query('month'))
            : now();

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->with('restTimes')
            ->get();

        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $csvData = [];

        foreach ($attendances as $attendance) {
            // ①先に休憩合計を計算
            $totalRest = 0;
            foreach ($attendance->restTimes as $rest) {
                if ($rest->rest_start && $rest->rest_end) {
                    $totalRest += \Carbon\Carbon::parse($rest->rest_start)
                        ->diffInMinutes(\Carbon\Carbon::parse($rest->rest_end));
                }
            }
            $restHours = intdiv($totalRest, 60);
            $restMins = $totalRest % 60;

            // ②先に勤務合計を計算
            $totalWork = 0;
            if ($attendance->clock_in && $attendance->clock_out) {
                $totalWork = \Carbon\Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $totalRest;
            }
            $workHours = intdiv($totalWork, 60);
            $workMins = $totalWork % 60;

            // ③計算済みの値を配列に入れる
            $csvData[] = [
                $attendance->work_date,
                $attendance->clock_in ?? '',
                $attendance->clock_out ?? '',
                $totalRest > 0 ? sprintf('%d:%02d', $restHours, $restMins) : '',
                $totalWork > 0 ? sprintf('%d:%02d', $workHours, $workMins) : '',
            ];
        }

        $csvContent = implode(',', $csvHeader) . "\n";
        foreach ($csvData as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="attendance.csv"');
    }
}
