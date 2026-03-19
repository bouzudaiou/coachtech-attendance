<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    protected $fillable = [
        'attendance_id',
        'user_id',
        'clock_in',
        'clock_out',
        'remarks',
        'status'
    ];

    public function attendance()
    {
    return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
