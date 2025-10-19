<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Http\Requests\AttendanceRequest;

class AttendanceActionController extends Controller
{
    public function updateOrCreate(AttendanceRequest $request)
    {
        $validated = $request->validated();

        $userId   = $validated['user_id'];
        $clockIn  = $validated['clock_in'] ?? null;
        $clockOut = $validated['clock_out'] ?? null;
        $remarks  = $validated['remarks'] ?? null;
        $date     = $validated['date'] ?? null;

        // 対象の勤怠データを取得
        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        // データが存在しない場合はエラー
        if (!$attendance) {
            return response()->json([
                'message' => '指定された勤怠データが存在しません',
            ], 404);
        }

        // pending の勤怠は承認待ちのため修正不可
        if ($attendance->status === 'pending') {
            return response()->json([
                'message' => '承認待ちの勤怠は修正できません',
            ], 422);
        }

        // 修正可能（normal / approved のみ）
        $attendance->update([
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'remarks'   => $remarks,
            // status, submitted_at, approved_at は変更しない
        ]);

        // 休憩時間を再登録
        $attendance->breakTimes()->delete();
        foreach ($validated['breakTimes'] ?? [] as $b) {
            if ($b['break_start'] || $b['break_end']) {
                $attendance->breakTimes()->create([
                    'break_start' => $b['break_start'],
                    'break_end'   => $b['break_end'],
                ]);
            }
        }

        return response()->json([
            'message' => '勤怠データを更新しました',
            'attendance_id' => $attendance->id,
        ]);
    }

    public function approve($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'status' => 'approved',
            'submitted_at' => $attendance->submitted_at ?? now(),
            'approved_at' => now(),
        ]);

        return response()->json(['message' => '承認が完了しました']);
    }
}

