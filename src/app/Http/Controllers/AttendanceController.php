<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;

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

    public function store(Request $request, $id = null)
    {
        $action = $request->input('action');
        $now = now();

        if ($action === 'clock_in') {
            Attendance::create([
                'user_id' => auth()->user()->id,
                'work_date' => $now->toDateString(),
                'clock_in' => $now->toTimeString(),
                'status' => '出勤中',
            ]);
        } elseif ($action === 'clock_out') {
            $attendance = Attendance::where('user_id', auth()->user()->id)
                ->where('work_date', $now->toDateString())
                ->firstOrFail();

            $attendance->update([
                'clock_out' => $now->toTimeString(),
                'status' => '退勤済',
            ]);
        } elseif ($action === 'rest_start') {

            $attendance = Attendance::where('user_id', auth()->user()->id)
                ->where('work_date', $now->toDateString())
                ->firstOrFail();

            $attendance->restTimes()->create([
                'rest_start' => $now->toTimeString(),
            ]);

            $attendance->update([
                'status' => '休憩中'
            ]);
        }elseif($action === 'rest_end'){

            $attendance = Attendance::where('user_id', auth()->user()->id)
                ->where('work_date', $now->toDateString())
                ->firstOrFail();

            $restTime = $attendance->restTimes()
                ->whereNull('rest_end')
                ->firstOrFail();

            $restTime->update([
                'rest_end' => $now->toTimeString(),
            ]);

            $attendance->update([
                'status' => '出勤中'
            ]);
        }elseif($action === 'correction') {
            $request->validate(
                (new AttendanceRequest())->rules(),
                (new AttendanceRequest())->messages()
            );

            $attendance = Attendance::findOrFail($id);

            $attendance->stampCorrectionRequests()->create([
                'user_id' => auth()->user()->id,
                'clock_in' => $request->input('clock_in'),
                'clock_out' => $request->input('clock_out'),
                'remarks' => $request->input('remarks'),
                'status' => '承認待ち'
            ]);

        }

        return redirect('/attendance');
    }

    public function list(Request $request) {
        // クエリパラメーターがあればその月、なければ今月
        $currentMonth = $request->query('month')
            ? \Carbon\Carbon::parse($request->query('month'))
            : now();

        $attendances = Attendance::whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->where('user_id', auth()->user()->id)
            ->with('restTimes')
            ->get();

        // コントローラーで全日付を生成してBladeに渡す
        $days = \Carbon\CarbonPeriod::create(
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        );

// attendancesをwork_dateをキーにした連想配列に変換
        $attendanceMap = $attendances->keyBy(function($a) {
            return $a->work_date;
        });

        return view('attendance.list', compact('days', 'attendanceMap', 'currentMonth'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('user', 'restTimes')->findOrFail($id);
        $restTimes = $attendance->restTimes;
        $isPending = $attendance->stampCorrectionRequests()
            ->where('status', '承認待ち')
            ->exists();

        return view('attendance.detail', compact('attendance', 'restTimes', 'isPending'));
    }
}
