<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function getList(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month') ?: date('Y-m');
        $month = str_replace('-', '/', $month);

        [$year, $monthNum] = explode('/', $month) + [date('Y'), date('m')];

        $start = "{$year}-{$monthNum}-01";
        $end = date('Y-m-t', strtotime($start));

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        $records = $attendances->map(function ($a) {
            $totalMinutes = $a->clock_in && $a->clock_out
                ? round((strtotime($a->clock_out) - strtotime($a->clock_in)) / 60)
                : null;

            $breakMinutes = $a->breakTimes->sum(function ($b) {
                if ($b->break_start && $b->break_end) {
                    return round((strtotime($b->break_end) - strtotime($b->break_start)) / 60);
                }
                return 0;
            });

            return [
                'id' => $a->id,
                'date' => $a->work_date,
                'clock_in' => $a->clock_in,
                'clock_out' => $a->clock_out,
                'break_minutes' => $breakMinutes,
                'total_minutes' => isset($totalMinutes)
                    ? max($totalMinutes - $breakMinutes, 0)
                    : null,
            ];
        });

        return response()->json([
            'records' => $records,
        ]);
    }

    public function getDetail($id)
    {
        $attendance = Attendance::with('breakTimes', 'user')->find($id);

        if (!$attendance) {
            return response()->json(['message' => 'データが見つかりません'], 404);
        }

        if (Auth::user()->role === 'user' && $attendance->user_id !== Auth::id()) {
            return response()->json(['message' => 'アクセス権がありません'], 403);
        }

        return response()->json([
            'user_name'  => $attendance->user->name,
            'date'       => $attendance->work_date,
            'clock_in'   => $attendance->clock_in,
            'clock_out'  => $attendance->clock_out,
            'remarks'    => $attendance->remarks,
            'breakTimes' => $attendance->breakTimes->map(fn ($b) => [
                'break_start' => $b->break_start,
                'break_end'   => $b->break_end,
            ]),
            'status'     => $attendance->status,
        ]);
    }

    public function getRequestList(Request $request)
    {
        $userId = Auth::id();
        $status = $request->query('status');

        $query = Attendance::with('user')
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at');

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        }

        return response()->json(
            $query->orderBy('submitted_at', 'desc')->get()
        );
    }
}
