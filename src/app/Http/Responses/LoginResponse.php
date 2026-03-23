<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if (auth()->user()->role === 'admin') {
            return redirect('/admin/attendance/list');
        }

        return redirect('/attendance');
    }
}
