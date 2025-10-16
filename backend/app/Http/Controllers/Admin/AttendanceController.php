<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

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
                'total_minutes' => isset($totalMinutes)
                    ? max($totalMinutes - $breakMinutes, 0)
                    : null,
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
            'break_times' => $attendance->breakTimes->map(fn($b) => [
                'break_start' => $b->break_start,
                'break_end'   => $b->break_end,
            ]),
            'status'     => $attendance->status,
        ]);
    }

    public function getListByStaff(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        if ($staff->role !== 'user') {
            abort(404);
        }

        // ===== 月の決定 =====
        $monthParam = $request->query('month');
        if (!empty($monthParam) && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            [$year, $monthNum] = explode('-', $monthParam);
        } else {
            $year = date('Y');
            $monthNum = date('m');
        }

        $start = sprintf('%04d-%02d-01', $year, $monthNum);
        $end = date('Y-m-t', strtotime($start));

        // ===== 勤怠一覧取得 =====
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        $records = $attendances->map(function ($a) {
            $workMinutes = ($a->clock_in && $a->clock_out)
                ? round((strtotime($a->clock_out) - strtotime($a->clock_in)) / 60)
                : null;

            $breakMinutes = $a->breakTimes->sum(function ($b) {
                if ($b->break_start && $b->break_end) {
                    return round((strtotime($b->break_end) - strtotime($b->break_start)) / 60);
                }
                return 0;
            });

            return [
                'id'            => $a->id,
                'date'          => $a->work_date,
                'clock_in'      => $a->clock_in,
                'clock_out'     => $a->clock_out,
                'break_minutes' => $breakMinutes,
                'total_minutes' => $workMinutes ? max(0, $workMinutes - $breakMinutes) : null,
            ];
        });

        return response()->json([
            'staff'   => [
                'id'   => $staff->id,
                'name' => $staff->name,
            ],
            'records' => $records,
        ]);
    }

    public function getRequestList(Request $request)
    {
        $status = $request->query('status');

        $query = Attendance::with('user')
            ->whereNotNull('submitted_at');

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        }

        return response()->json([
            'records' => $query->orderBy('submitted_at', 'desc')->get(),
        ]);
    }
}
