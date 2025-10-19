<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\CarbonPeriod;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザーを1人作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 固定の一般ユーザー（ログイン確認用）
        $fixedUser = User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('user1234'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // --- 固定ユーザーの勤怠データ生成 ---
        $this->generateAttendanceData($fixedUser);

        // その他一般ユーザー数を .env から取得
        $userCount = env('SEED_USER_COUNT', 10);

        // ランダムユーザー作成＋勤怠データ生成
        User::factory($userCount)->create([
            'role' => 'user',
        ])->each(function ($user) {
            $this->generateAttendanceData($user);
        });
    }

    /**
     * 指定ユーザーに勤怠・休憩データを生成
     */
    private function generateAttendanceData(User $user): void
    {
        $period = CarbonPeriod::create('2025-09-20', '2025-11-10');

        foreach ($period as $date) {
            if ($date->isWeekend()) {
                continue; // 土日は除外
            }

            // 出勤記録を作成
            $attendanceData = Attendance::factory()->make([
                'user_id'   => $user->id,
                'work_date' => $date->format('Y-m-d'),
                'clock_in'  => fake()->dateTimeBetween($date->format('Y-m-d').' 08:00:00', $date->format('Y-m-d').' 10:00:00'),
                'clock_out' => fake()->dateTimeBetween($date->format('Y-m-d').' 17:00:00', $date->format('Y-m-d').' 20:00:00'),
            ])->toArray();

            // submitted_at は work_date の翌日に固定（pending/approved の場合のみ）
            if (in_array($attendanceData['status'], ['pending', 'approved'])) {
                $attendanceData['submitted_at'] = (clone $date)->addDay()->setTime(rand(9, 18), rand(0, 59));
            }

            $attendance = Attendance::create($attendanceData);

            $workStart = $attendance->clock_in;
            $workEnd   = $attendance->clock_out;

            // この日の休憩回数（0〜2回）
            $breakCount = rand(0, 2);

            $currentStart = clone $workStart;

            for ($i = 0; $i < $breakCount; $i++) {
                $latestPossibleStart = (clone $workEnd)->modify('-2 hours');

                if ($currentStart >= $latestPossibleStart) {
                    break;
                }

                $breakStart = fake()->dateTimeBetween($currentStart, $latestPossibleStart);
                $breakEnd = (clone $breakStart)->modify('+' . rand(1, 2) . ' hours');

                if ($breakEnd >= $workEnd) {
                    $breakEnd = (clone $workEnd)->modify('-1 minute');
                }

                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    'break_start'   => $breakStart,
                    'break_end'     => $breakEnd,
                ]);

                $currentStart = (clone $breakEnd);
            }
        }
    }
}
