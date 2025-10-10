<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStatusController extends Controller
{
    public function getStatus()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $datetime = now()
            ->locale('ja')
            ->isoFormat('YYYY年MM月DD日(ddd) HH:mm');


        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->with('breakTimes')
            ->first();

        if (!$attendance) {
            return response()->json([
                'datetime'   => $datetime,
                'status'     => '勤務外',
                'attendance' => null,
            ]);
        }

        if ($attendance->clock_out) {
            $status = '退勤済';
        } else {
            $latestBreak = $attendance->breakTimes()->orderBy('id', 'desc')->first();
            if ($latestBreak && !$latestBreak->break_end) {
                $status = '休憩中';
            } elseif ($attendance->clock_in) {
                $status = '出勤中';
            } else {
                $status = '勤務外';
            }
        }

        return response()->json([
            'datetime'   => $datetime,
            'status' => $status,
            'attendance' => $attendance,
        ]);
    }
}
