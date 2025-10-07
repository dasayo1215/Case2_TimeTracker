<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function getList(Request $request)
    {
        $date = $request->query('date') ?: date('Y-m-d');

        $attendances = Attendance::with(['user', 'breakTimes'])
            ->where('work_date', $date)
            ->orderBy('user_id')
            ->get();

        $records = $attendances->map(function ($a) {
            $totalMinutes = null;
            if ($a->clock_in && $a->clock_out) {
                $totalMinutes = round((strtotime($a->clock_out) - strtotime($a->clock_in)) / 60);
            }

            $breakMinutes = $a->breakTimes->sum(function ($b) {
                if ($b->break_start && $b->break_end) {
                    return round((strtotime($b->break_end) - strtotime($b->break_start)) / 60);
                }
                return 0;
            });

            return [
                'id' => $a->id,
                'user' => [
                    'id' => $a->user->id,
                    'name' => $a->user->name,
                ],
                'clock_in' => $a->clock_in,
                'clock_out' => $a->clock_out,
                'break_minutes' => $breakMinutes,
                'total_minutes' => $totalMinutes ? max($totalMinutes - $breakMinutes, 0) : null,
            ];
        });

        return response()->json($records);
    }

    public function getDetail($id)
    {
        $attendance = Attendance::with('breakTimes', 'user')->find($id);

        if (!$attendance) {
            return response()->json(['message' => 'データが見つかりません'], 404);
        }

        return response()->json([
            'user_name'  => $attendance->user->name,
            'user_id'    => $attendance->user_id,
            'date'       => $attendance->work_date,
            'clock_in'   => $attendance->clock_in,
            'clock_out'  => $attendance->clock_out,
            'remarks'    => $attendance->remarks,
            'breakTimes' => $attendance->breakTimes->map(fn($b) => [
                'break_start' => $b->break_start,
                'break_end'   => $b->break_end,
            ]),
            'status'     => $attendance->status,
        ]);
    }
}
