<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

class AttendanceActionController extends Controller
{
    public function updateOrCreate(AttendanceRequest $request, $id)
    {
        $validated = $request->validated();

        $userId   = $validated['user_id'];
        $clockIn  = $validated['clock_in'] ?? null;
        $clockOut = $validated['clock_out'] ?? null;
        $remarks  = $validated['remarks'] ?? null;
        $date     = $validated['date'] ?? null;

        // 該当日の勤怠データを取得
        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        if ($attendance) {
            // ====== 既存レコードあり ======
            if ($attendance->status === 'normal') {
                // normal → approved に昇格して修正を反映
                $attendance->update([
                    'clock_in'     => $clockIn,
                    'clock_out'    => $clockOut,
                    'remarks'      => $remarks,
                    'status'       => 'approved',
                    'submitted_at' => now(),
                    'approved_at'  => now(),
                ]);

                // 休憩再登録
                $attendance->breakTimes()->delete();
                foreach ($validated['breakTimes'] ?? [] as $b) {
                    if ($b['break_start'] || $b['break_end']) {
                        $attendance->breakTimes()->create([
                            'break_start' => $b['break_start'],
                            'break_end'   => $b['break_end'],
                        ]);
                    }
                }
            } else {
                // approved の場合 → 修正を受け付けない
                return response()->json([
                    'message' => 'この勤怠データはすでに承認済みです（変更不可）',
                ], 400);
            }
        } else {
            // ====== 新規レコード（念のため対応） ======
            $attendance = Attendance::create([
                'user_id'      => $userId,
                'work_date'    => $date,
                'clock_in'     => $clockIn,
                'clock_out'    => $clockOut,
                'remarks'      => $remarks,
                'status'       => 'approved',
                'submitted_at' => now(),
                'approved_at'  => now(),
            ]);

            foreach ($validated['breakTimes'] ?? [] as $b) {
                if ($b['break_start'] || $b['break_end']) {
                    $attendance->breakTimes()->create([
                        'break_start' => $b['break_start'],
                        'break_end'   => $b['break_end'],
                    ]);
                }
            }
        }

        return response()->json([
            'message' => '勤怠データを更新しました（approved）',
            'attendance_id' => $attendance->id,
        ]);
    }
}
