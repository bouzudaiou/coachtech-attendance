<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\RestTime;
use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceRequest;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->query('date')
            ? \Carbon\Carbon::parse($request->query('date'))
            : now();

        $attendances = Attendance::where('work_date', $currentDate->toDateString())
            ->with('user', 'restTimes')
            ->get();

        return view('admin.attendance.list', compact('attendances', 'currentDate'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('user', 'restTimes')->findOrFail($id);
        $restTimes = $attendance->restTimes;

        return view('admin.attendance.detail', compact('attendance', 'restTimes'));
    }

    public function update(AdminAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('restTimes')->findOrFail($id);

        $attendance->update([
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'remarks' => $request->input('remarks'),
        ]);

        foreach ($request->input('rest_id') as $index => $restId) {
            if ($restId) {
                // 既存レコードの更新
                RestTime::find($restId)->update([
                    'rest_start' => $request->input('rest_start')[$index],
                    'rest_end' => $request->input('rest_end')[$index],
                ]);
            } else {
                // 新規レコードの作成（追加休憩行に入力があった場合）
                $attendance->restTimes()->create([
                    'rest_start' => $request->input('rest_start')[$index],
                    'rest_end' => $request->input('rest_end')[$index],
                ]);
            }
        }

        return redirect('/admin/attendance/list');
    }
}
