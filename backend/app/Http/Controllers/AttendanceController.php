<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function getStatus()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->with('breakTimes')
            ->first();

        if (!$attendance) {
            return response()->json(['status' => '勤務外']);
        }

        // 状態判定ロジック
        if ($attendance->clock_out) {
            $status = '退勤済';
        } else {
            $latestBreak = $attendance->breakTimes()
                ->orderBy('id', 'desc')
                ->first();

            if ($latestBreak && !$latestBreak->break_end) {
                $status = '休憩中';
            } else if ($attendance->clock_in) {
                $status = '出勤中';
            } else {
                $status = '勤務外';
            }
        }

        return response()->json([
            'status' => $status,
            'attendance' => $attendance,
        ]);
    }

    public function clock(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // 今日の勤怠を取得 or 新規作成
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => 'normal']
        );

        switch ($request->action) {
            case 'clock_in':
                $attendance->clock_in = $now->format('H:i:s');
                $attendance->save();
                break;

            case 'clock_out':
                $attendance->clock_out = $now->format('H:i:s');
                $attendance->save();
                break;

            case 'break_start':
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $now->format('H:i:s'),
                ]);
                break;

            case 'break_end':
                $break = BreakTime::where('attendance_id', $attendance->id)
                    ->whereNull('break_end')
                    ->latest()
                    ->first();
                if ($break) {
                    $break->break_end = $now->format('H:i:s');
                    $break->save();
                }
                break;
        }

        return response()->json([
            'message' => '打刻を記録しました',
            'attendance' => $attendance->fresh('breakTimes'),
        ]);
    }

    public function getList(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month'); // 例: "2025-10" or "2025/10" or null

        // 🔹 安全対策: 値チェックとフォーマット統一
        if (empty($month)) {
            // デフォルトは今月
            $month = date('Y-m');
        }

        // "-" を "/" に変換
        $month = str_replace('-', '/', $month);

        // explodeに失敗しないように保険
        $parts = explode('/', $month);
        $year = $parts[0] ?? date('Y');
        $monthNum = $parts[1] ?? date('m');

        // 月初と月末を算出
        $start = "{$year}-{$monthNum}-01";
        $end = date('Y-m-t', strtotime($start));

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        $records = $attendances->map(function ($a) {
            $totalMinutes = null;
            if ($a->clock_in && $a->clock_out) {
                $totalMinutes = round((strtotime($a->clock_out) - strtotime($a->clock_in)) / 60);
            }

            $breakMinutes = $a->breakTimes->reduce(function ($carry, $b) {
                if ($b->break_start && $b->break_end) {
                    return $carry + round((strtotime($b->break_end) - strtotime($b->break_start)) / 60);
                }
                return $carry;
            }, 0);

            $netMinutes = $totalMinutes !== null ? max($totalMinutes - $breakMinutes, 0) : null;

            return [
                'id' => $a->id,
                'date' => $a->work_date,
                'start_time' => $a->clock_in,
                'end_time' => $a->clock_out,
                'break_minutes' => $breakMinutes,
                'total_minutes' => $netMinutes,
            ];
        });

        return response()->json($records);
    }

    public function getDetail($id)
    {
        $attendance = Attendance::with('breakTimes', 'user')
            ->find($id);

        if ($attendance) {
            return response()->json([
                'user_name' => $attendance->user->name,
                'date' => $attendance->work_date,
                'clock_in' => $attendance->clock_in,
                'clock_out' => $attendance->clock_out,
                'remarks' => $attendance->remarks,
                'breakTimes' => $attendance->breakTimes
                    ? $attendance->breakTimes->map(fn ($b) => [
                        'break_start' => $b->break_start,
                        'break_end' => $b->break_end,
                    ])
                    : [],
                'status' => $attendance->status,
            ]);
        }

        // ===== 該当データなしの場合 =====
        $user = Auth::user();
        // 例: id=2025-10-06 のように「日付」をパラメータで渡してるなら
        $date = $id; // 必要に応じて変換

        return response()->json([
            'user_name' => $user->name,
            'date' => $date,
            'clock_in' => null,
            'clock_out' => null,
            'note' => '',
            'breakTimes' => [],
        ]);
    }

    public function updateOrCreate(AttendanceRequest $request, $id)
    {
        $user = Auth::user();
        $validated = $request->validated();

        $clockIn  = $validated['clock_in'] ?? null;
        $clockOut = $validated['clock_out'] ?? null;
        $remarks  = $validated['remarks'] ?? '修正申請';
        $date     = $validated['date'] ?? null;

        // 既存レコードを探す
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $date)
            ->first();

        if ($attendance) {
            $attendance->update([
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'remarks' => $remarks,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);
            $attendance->breakTimes()->delete();
        } else {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'remarks' => $remarks,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);
        }

        foreach ($validated['breakTimes'] ?? [] as $b) {
            if ($b['break_start'] || $b['break_end']) {
                $attendance->breakTimes()->create([
                    'break_start' => $b['break_start'],
                    'break_end'   => $b['break_end'],
                ]);
            }
        }

        return response()->json([
            'message' => '勤怠データを申請しました（pending）',
            'attendance_id' => $attendance->id,
        ]);
    }
}
