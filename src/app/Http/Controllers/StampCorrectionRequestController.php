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
                ->with('user')
                ->get();
            $approvedRequests = StampCorrectionRequest::where('status', '承認済み')
                ->with('user')
                ->get();
        } else {
            $user = auth()->user();
            $pendingRequests = StampCorrectionRequest::where('user_id', $user->id)
                ->where('status', '承認待ち')
                ->get();

            $approvedRequests = StampCorrectionRequest::where('user_id', $user->id)
                ->where('status', '承認済み')
                ->get();
        }
        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }

    public function show($attendance_correct_request_id) {


    }


}
