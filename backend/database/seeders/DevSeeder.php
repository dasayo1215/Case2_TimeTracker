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

        // 一般ユーザー数を .env から取得
        $userCount = env('SEED_USER_COUNT', 10);

        User::factory($userCount)->create([
            'role' => 'user',
        ])->each(function ($user) {
            // 2025-08-20 ～ 2025-10-10 の日付範囲を作成
            $period = CarbonPeriod::create('2025-08-20', '2025-10-10');

            foreach ($period as $date) {
                if ($date->isWeekend()) {
                    continue; // 土日は除外
                }

                // 出勤記録を作成
                $attendance = Attendance::factory()->create([
                    'user_id'   => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                    'clock_in'  => fake()->dateTimeBetween($date->format('Y-m-d').' 08:00:00', $date->format('Y-m-d').' 10:00:00'),
                    'clock_out' => fake()->dateTimeBetween($date->format('Y-m-d').' 17:00:00', $date->format('Y-m-d').' 20:00:00'),
                ]);

                $workStart = $attendance->clock_in;
                $workEnd   = $attendance->clock_out;

                // この日の休憩回数（0〜2回）
                $breakCount = rand(0, 2);

                $currentStart = clone $workStart;

                for ($i = 0; $i < $breakCount; $i++) {
                    // 勤務終了の2時間前までに休憩を始めないと「1時間休憩」が作れない
                    $latestPossibleStart = (clone $workEnd)->modify('-2 hours');

                    // 範囲が逆転してたら休憩を入れられない → break
                    if ($currentStart >= $latestPossibleStart) {
                        break;
                    }

                    // 休憩開始は「前の休憩の終了時刻」以降〜勤務終了2時間前まで
                    $breakStart = fake()->dateTimeBetween($currentStart, $latestPossibleStart);

                    // 休憩時間は1〜2時間
                    $breakEnd = (clone $breakStart)->modify('+' . rand(1, 2) . ' hours');

                    // 休憩終了が退勤時間以上なら「退勤の1分前」に丸める
                    if ($breakEnd >= $workEnd) {
                        $breakEnd = (clone $workEnd)->modify('-1 minute');
                    }

                    BreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => $breakStart,
                        'break_end'     => $breakEnd,
                    ]);

                    // 次の休憩はこの休憩終了後から
                    $currentStart = (clone $breakEnd);
                }
            }
        });
    }
}
