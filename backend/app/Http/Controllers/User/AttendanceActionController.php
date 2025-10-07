<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

class AttendanceActionController extends Controller
{
    public function clock(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => 'normal']
        );

        switch ($request->action) {
            case 'clock_in':
                $attendance->update(['clock_in' => $now->format('H:i:s')]);
                break;
            case 'clock_out':
                $attendance->update(['clock_out' => $now->format('H:i:s')]);
                break;
            case 'break_start':
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $now->format('H:i:s'),
                ]);
                break;
            case 'break_end':
                $break = $attendance->breakTimes()->whereNull('break_end')->latest()->first();
                if ($break) {
                    $break->update(['break_end' => $now->format('H:i:s')]);
                }
                break;
        }

        return response()->json([
            'message' => '打刻を記録しました',
            'attendance' => $attendance->fresh('breakTimes'),
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

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'work_date' => $date],
            [
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'remarks' => $remarks,
                'status' => 'pending',
                'submitted_at' => now(),
            ]
        );

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
            'message' => '勤怠データを申請しました（pending）',
            'attendance_id' => $attendance->id,
        ]);
    }
}
