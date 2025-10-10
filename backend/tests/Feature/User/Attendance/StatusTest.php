<?php

namespace Tests\Feature\User\Attendance;

use Tests\Feature\User\UserTestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;

class StatusTest extends UserTestCase
{
    // 現在の日時情報がUIと同じ形式で出力されている
    public function test_returns_current_datetime_that_exactly_matches_ui_format_and_current_time(): void
    {
        // 1. 勤怠打刻画面を開く（＝API呼び出し）
        $response = $this->getJsonAsUser('/api/attendance/status');

        // 2. 画面に表示されている日時情報を確認する
        $response->assertStatus(200)->assertJsonStructure(['datetime']);

        $data = $response->json();
        $datetime = $data['datetime'];

        // --- UIで表示される日時フォーマットの検証
        $this->assertMatchesRegularExpression(
            '/^\d{4}年\d{1,2}月\d{1,2}日[（(].+?[）)]\s?\d{2}:\d{2}$/u',
            $datetime,
            'datetime の形式は「YYYY年M月D日(曜) HH:MM」である必要があります。'
        );

        // --- 時刻一致（UIは分単位表示を想定）：同じ「分」であることをチェック
        $tz = config('app.timezone', 'Asia/Tokyo');
        $expectedNow = Carbon::now($tz); // 現在時刻

        // 曜日部分（カッコ内）を除去してからパース（ASCII／全角どちらにも対応）
        $cleanDatetime = preg_replace('/[（(].+?[）)]/u', '', $datetime);
        // "YYYY年M月D日 HH:MM" をパース（先頭ゼロなし対応の n/j を使用）
        $apiTime = Carbon::createFromFormat('Y年n月j日 H:i', trim($cleanDatetime), $tz);

        $this->assertTrue(
            $expectedNow->isSameMinute($apiTime),
            'UI上の日時と現在時刻の「分」が一致していません。'
        );
    }

    // 勤務外の場合、勤怠ステータスが正しく表示される
    public function test_returns_status_not_working_when_no_attendance_exists(): void
    {
        // 1. ステータスが勤務外のユーザーにログイン（UserTestCaseで認証済）
        // 2. 勤怠打刻画面（API）を開く
        $response = $this->getJsonAsUser('/api/attendance/status');

        // 3. ステータス確認
        $response->assertStatus(200)
            ->assertJsonPath('status', '勤務外');
    }

    // 出勤中の場合、勤怠ステータスが正しく表示される
    public function test_returns_status_working_when_clocked_in_but_not_out(): void
    {
        $tz = config('app.timezone', 'Asia/Tokyo');

        // 1. ステータスが出勤中になるデータを作成（本日、出勤済み・退勤未実施）
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::now($tz)->toDateString(),
            'clock_in'  => Carbon::now($tz)->subHours(2),
            'clock_out' => null,
        ]);

        // 2. 画面（API）を開く
        $response = $this->getJsonAsUser('/api/attendance/status');

        // 3. ステータス確認
        $response->assertStatus(200)
            ->assertJsonPath('status', '出勤中');
    }

    // 休憩中の場合、勤怠ステータスが正しく表示される
    public function test_returns_status_on_break_when_break_started_but_not_ended(): void
    {
        $tz = config('app.timezone', 'Asia/Tokyo');

        // 1. 出勤中の勤怠 + 休憩開始済み・休憩終了未実施
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::now($tz)->toDateString(),
            'clock_in'  => Carbon::now($tz)->subHours(2),
            'clock_out' => null,
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start'   => Carbon::now($tz)->subMinutes(30),
            'break_end'     => null,
        ]);

        // 2. 画面（API）を開く
        $response = $this->getJsonAsUser('/api/attendance/status');

        // 3. ステータス確認
        $response->assertStatus(200)
            ->assertJsonPath('status', '休憩中');
    }

    // 退勤済の場合、勤怠ステータスが正しく表示される
    public function test_returns_status_left_work_when_clock_out_exists(): void
    {
        $tz = config('app.timezone', 'Asia/Tokyo');

        // 1. 本日、出勤・退勤済みの勤怠を作成
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::now($tz)->toDateString(),
            'clock_in'  => Carbon::now($tz)->subHours(8),
            'clock_out' => Carbon::now($tz)->subHours(1),
        ]);

        // 2. 画面（API）を開く
        $response = $this->getJsonAsUser('/api/attendance/status');

        // 3. ステータス確認
        $response->assertStatus(200)
            ->assertJsonPath('status', '退勤済');
    }
}
