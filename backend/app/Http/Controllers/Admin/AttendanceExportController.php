<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

class AttendanceExportController extends Controller
{
    public function exportCsv(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        // ===== 月指定（例: 2025-10） =====
        [$year, $monthNum] = explode('-', $request->query('month') ?? now()->format('Y-m'));
        $start = "{$year}-{$monthNum}-01";

        // ===== 勤怠データ取得 =====
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, date('Y-m-t', strtotime($start))])
            ->orderBy('work_date')
            ->get();

        // ===== CSV出力処理 =====
        $callback = function () use ($attendances, $year, $monthNum) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Excel文字化け対策
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩時間', '合計時間', '備考', 'ステータス']);

            $attendanceMap = $attendances->keyBy('work_date');
            $daysInMonth = (int) date('t', strtotime("{$year}-{$monthNum}-01"));
            $toHHMM = fn($m) => sprintf('%02d:%02d', floor($m / 60), $m % 60);
            $statusLabel = ['normal' => '通常', 'pending' => '申請中', 'approved' => '承認済み'];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $monthNum, $day);
                $a = $attendanceMap->get($date);

                if ($a) {
                    $breakMin = $a->breakTimes->sum(fn($b) =>
                        ($b->break_start && $b->break_end)
                            ? round((strtotime($b->break_end) - strtotime($b->break_start)) / 60)
                            : 0
                    );

                    $totalMin = ($a->clock_in && $a->clock_out)
                        ? round((strtotime($a->clock_out) - strtotime($a->clock_in)) / 60) - $breakMin
                        : null;

                    fputcsv($handle, [
                        $a->work_date,
                        $a->clock_in ? substr($a->clock_in, 0, 5) : '',
                        $a->clock_out ? substr($a->clock_out, 0, 5) : '',
                        $toHHMM($breakMin),
                        $totalMin !== null ? $toHHMM($totalMin) : '',
                        $a->remarks,
                        $statusLabel[$a->status] ?? $a->status,
                    ]);
                } else {
                    fputcsv($handle, [$date, '', '', '', '', '', '']);
                }
            }
            fclose($handle);
        };

        // ===== ダウンロード実行 =====
        $filename = sprintf('%s（%04d年%02d月）勤怠.csv', $staff->name, $year, $monthNum);

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
