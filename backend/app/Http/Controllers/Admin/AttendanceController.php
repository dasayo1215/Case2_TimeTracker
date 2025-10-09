<?php

namespace App\Http\Controllers\Admin;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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
            'breakTimes' => $attendance->breakTimes->map(fn($b) => [
                'break_start' => $b->break_start,
                'break_end'   => $b->break_end,
            ]),
            'status'     => $attendance->status,
        ]);
    }

    public function getListByStaff(Request $request, $id)
    {
        $staff = User::findOrFail($id); // ← 手動で取得！

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

        return response()->json(
            $query->orderBy('submitted_at', 'desc')->get()
        );
    }

    public function exportCsv(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        // ===== 現在表示中の月を取得 =====
        $monthParam = $request->query('month'); // 例: 2025-10
        if ($monthParam) {
            [$year, $monthNum] = explode('-', $monthParam);
        } else {
            $year = now()->year;
            $monthNum = now()->month;
        }

        $start = "{$year}-{$monthNum}-01";
        $end = date('Y-m-t', strtotime($start));

        // ===== 指定月の勤怠データ取得 =====
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date', 'asc')
            ->get();

        $response = new StreamedResponse(function () use ($attendances, $staff, $year, $monthNum) {
            $handle = fopen('php://output', 'w');

            // Excel文字化け対策（BOM）
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩時間', '合計時間', '備考', 'ステータス']);

            // 勤怠データを日付キーで参照しやすくしておく
            $attendanceMap = $attendances->keyBy('work_date');

            // その月の日数を算出
            $daysInMonth = (int) date('t', strtotime("{$year}-{$monthNum}-01"));

            // 月の全日ループ
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $monthNum, $day);
                $a = $attendanceMap->get($date);

                if ($a) {
                    // 休憩時間（分→HH:MM）
                    $breakMinutes = $a->breakTimes->sum(function ($b) {
                        if ($b->break_start && $b->break_end) {
                            return round((strtotime($b->break_end) - strtotime($b->break_start)) / 60);
                        }
                        return 0;
                    });
                    $breakTimeStr = sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);

                    // 合計時間（分→HH:MM）
                    $totalMinutes = null;
                    if ($a->clock_in && $a->clock_out) {
                        $totalMinutes = round((strtotime($a->clock_out) - strtotime($a->clock_in)) / 60) - $breakMinutes;
                    }
                    $totalTimeStr = $totalMinutes !== null
                        ? sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60)
                        : '';

                    fputcsv($handle, [
                        $a->work_date,
                        $a->clock_in ? substr($a->clock_in, 0, 5) : '',
                        $a->clock_out ? substr($a->clock_out, 0, 5) : '',
                        $breakTimeStr,
                        $totalTimeStr,
                        $a->remarks,
                        match ($a->status) {
                            'normal' => '通常',
                            'pending' => '申請中',
                            'approved' => '承認済み',
                            default => $a->status,
                        },
                    ]);
                } else {
                    // データがない日は日付だけ
                    fputcsv($handle, [$date, '', '', '', '', '', '']);
                }
            }

            fclose($handle);
        });

        // ===== ファイル名に年月を含める =====
        $monthLabel = sprintf('%04d年%02d月', $year, $monthNum);
        $filename = sprintf('%s（%s）勤怠.csv', $staff->name, $monthLabel);

        // RFC 5987 対応（日本語ファイル名を確実に通す）
        $encoded = rawurlencode($filename);
        $disposition = "attachment; filename*=UTF-8''{$encoded}; filename=\"{$filename}\"";

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
