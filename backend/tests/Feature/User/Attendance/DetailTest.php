<?php

namespace Tests\Feature\User\Attendance;

use Tests\Feature\User\UserTestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class DetailTest extends UserTestCase
{
    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_attendance_detail_shows_logged_in_user_name(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログイン（UserTestCaseで認証済）

        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->getJsonAsUser('/api/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 3. 名前欄を確認する
        $data = $response->json();
        $record = $data['record'] ?? $data;

        $this->assertEquals(
            $this->user->name,
            $record['user_name'] ?? $record['name'] ?? null,
            '勤怠詳細画面の「名前」がログインユーザーの氏名になっていません'
        );
    }

    // 勤怠詳細画面の「日付」が選択した日付になっている
    public function test_attendance_detail_shows_selected_date(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログイン
        $selectedDate = Carbon::today()->toDateString();

        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => $selectedDate,
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->getJsonAsUser('/api/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 3. 日付欄を確認する
        $data = $response->json();
        $record = $data['record'] ?? $data;

        $this->assertEquals(
            $selectedDate,
            $record['work_date'] ?? $record['date'] ?? null,
            '勤怠詳細画面の日付が選択した日付と一致していません'
        );
    }

    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function test_attendance_detail_shows_correct_clock_in_and_out_times(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログイン
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:15:00',
            'clock_out' => '17:45:00',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->getJsonAsUser('/api/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 3. 出勤・退勤欄を確認する
        $data = $response->json();
        $record = $data['record'] ?? $data;

        $this->assertEquals(
            '09:15:00',
            $record['clock_in'],
            '勤怠詳細画面の出勤時刻が打刻情報と一致していません'
        );

        $this->assertEquals(
            '17:45:00',
            $record['clock_out'],
            '勤怠詳細画面の退勤時刻が打刻情報と一致していません'
        );
    }

    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function test_attendance_detail_shows_correct_break_times(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログイン
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 休憩時間を登録（複数対応確認も兼ねて2件）
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start'   => '12:00:00',
            'break_end'     => '12:45:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start'   => '15:00:00',
            'break_end'     => '15:15:00',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->getJsonAsUser('/api/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 3. 休憩欄を確認する
        $data = $response->json();
        $record = $data['record'] ?? $data;

        $this->assertNotEmpty($record['break_times'] ?? null, '休憩情報が取得できていません');

        $breaks = collect($record['break_times']);

        $this->assertTrue(
            $breaks->contains(fn ($b) => $b['break_start'] === '12:00:00' && $b['break_end'] === '12:45:00'),
            '最初の休憩時間が一致していません'
        );

        $this->assertTrue(
            $breaks->contains(fn ($b) => $b['break_start'] === '15:00:00' && $b['break_end'] === '15:15:00'),
            '2回目の休憩時間が一致していません'
        );
    }
}
