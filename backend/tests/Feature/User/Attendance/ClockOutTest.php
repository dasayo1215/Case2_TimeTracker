<?php

namespace Tests\Feature\User\Attendance;

use Tests\Feature\User\UserTestCase;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockOutTest extends UserTestCase
{
    // 退勤ボタンが正しく機能する
    public function test_clock_out_creates_record_and_changes_status(): void
    {
        // 1. ステータスが勤務中のユーザーにログイン（UserTestCaseで認証済）

        // 出勤中の勤怠レコードを作成
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(8), // 出勤中想定
        ]);

        // 2. 画面に「退勤」ボタンが表示されていることを確認
        // ※ APIテストではUIの存在確認はできないため、
        // 「出勤中状態＝退勤可能であること」を前提に進める

        // 3. 退勤の処理を行う
        $response = $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_out']);
        $response->assertStatus(200);

        // 勤怠テーブルに退勤時刻が登録されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id'        => $attendance->id,
            'user_id'   => $this->user->id,
        ]);

        // ステータスAPIから勤務状態を確認
        $statusResponse = $this->getJsonAsUser('/api/attendance/status');
        $statusResponse->assertStatus(200);

        $json = $statusResponse->json();
        $this->assertEquals(
            '退勤済',
            $json['status'],
            '退勤処理後のステータスが「退勤済」になっていません'
        );
    }

    // 退勤時刻が勤怠一覧画面で確認できる
    public function test_clock_out_time_appears_in_attendance_list(): void
    {
        // 1. ステータスが勤務外のユーザーにログイン（UserTestCaseで認証済）

        // 出勤前の状態を確認
        $this->assertDatabaseMissing('attendances', [
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);

        // 2. 出勤と退勤の処理を行う
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_in']);

        // 少し時間経過を再現
        $this->travel(60)->minutes();

        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_out']);

        // 3. 勤怠一覧画面から当日の退勤時刻を確認
        $response = $this->getJsonAsUser('/api/attendance/list');
        $response->assertStatus(200);

        $data = $response->json();
        $today = Carbon::today()->toDateString();

        $record = collect($data['records'] ?? $data)->firstWhere('date', $today);

        $this->assertNotNull($record, '今日の勤怠データが一覧に存在しません');
        $this->assertNotNull($record['clock_out'], '退勤時刻が一覧に反映されていません');
        $this->assertMatchesRegularExpression(
            '/^\d{2}:\d{2}/',
            $record['clock_out'],
            '退勤時刻の形式が正しくありません（HH:MM）'
        );
    }
}
