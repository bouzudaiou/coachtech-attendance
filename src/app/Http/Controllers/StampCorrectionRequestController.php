<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            $pendingRequests = StampCorrectionRequest::where('status', '承認待ち')
                ->with(['user', 'attendance'])
                ->get();
            $approvedRequests = StampCorrectionRequest::where('status', '承認済み')
                ->with(['user', 'attendance'])
                ->get();
        } else {
            $user = auth()->user();
            $pendingRequests = StampCorrectionRequest::where('user_id', $user->id)
                ->where('status', '承認待ち')
                ->with(['user', 'attendance'])
                ->get();

            $approvedRequests = StampCorrectionRequest::where('user_id', $user->id)
                ->where('status', '承認済み')
                ->with(['user', 'attendance'])
                ->get();
        }
        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }

    public function show($attendance_correct_request_id)
    {
        $correctionRequest = StampCorrectionRequest::with(['attendance.restTimes', 'attendance.user'])
            ->findOrFail($attendance_correct_request_id);

        $attendance = $correctionRequest->attendance;
        $restTimes = $attendance->restTimes;
        $isApproved = $correctionRequest->status === '承認済み';

        return view('stamp_correction_request.approve', compact('correctionRequest', 'attendance', 'restTimes', 'isApproved'));
    }

    public function update($attendance_correct_request_id)
    {
        $correctionRequest = StampCorrectionRequest::with('attendance')->findOrFail($attendance_correct_request_id);
        $attendance = $correctionRequest->attendance;

        // 承認済みの場合は処理しない
        if ($correctionRequest->status === '承認済み') {
            return redirect('/stamp_correction_request/list');
        }

        $correctionRequest->update([
            'status' => '承認済み'
        ]);

        $attendance->update([
            'clock_in' => $correctionRequest->clock_in,
            'clock_out' => $correctionRequest->clock_out,
        ]);

        return redirect('/stamp_correction_request/list');
    }
}
