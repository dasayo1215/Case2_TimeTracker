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

        // 一般ユーザー数を .env から取得
        $userCount = env('SEED_USER_COUNT', 10);

        User::factory($userCount)->create()->each(function ($user) {
            Attendance::factory(10)->create([
                'user_id' => $user->id,
            ])->each(function ($attendance) {
                $breakCount = rand(1, 3);
                BreakTime::factory($breakCount)->create([
                    'attendance_id' => $attendance->id,
                ]);
            });
        });
    }
}
