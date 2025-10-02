<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Hash;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザーを1人作成
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 一般ユーザーを5人作成
        User::factory(1000)->create()->each(function ($user) {
            // 各ユーザーに 10 日分の勤怠を作成
            Attendance::factory(10)->create([
                'user_id' => $user->id,
            ])->each(function ($attendance) {
                // 勤怠ごとに 1〜3 個の休憩をランダムで作成
                $breakCount = rand(1, 3);
                BreakTime::factory($breakCount)->create([
                    'attendance_id' => $attendance->id,
                ]);
            });
        });
    }
}
