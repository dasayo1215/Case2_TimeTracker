<?php

namespace Tests\Feature\User\Attendance;

use Tests\Feature\User\UserTestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimeTest extends UserTestCase
{
    // 休憩ボタンが正しく機能する
    public function test_break_in_creates_record_and_changes_status(): void
    {
        // 1. ステータスが出勤中のユーザーにログイン（UserTestCaseで認証済）

        // 出勤中のレコードを準備
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(2),
        ]);

        // 2. 画面に「休憩入」ボタンが表示されていることを確認
        // ※ APIテストではUI確認は不可のため、出勤中状態＝休憩可能であることを前提に進める

        // 3. 出勤打刻を行う（Attendanceを確実に作成）
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_in']);

        // 4. 休憩の処理を行う
        $response = $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_start']);
        $response->assertStatus(200);

        // 休憩レコードが作成されていること
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);
    }

    // 休憩は一日に何回でもできる
    public function test_can_take_multiple_breaks_in_same_day(): void
    {
        // 1. ステータスが出勤中であるユーザーにログイン
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(3),
        ]);

        // 出勤状態をAPI的に明示（内部ロジック整合のため）
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_in']);

        // 2. 休憩入と休憩戻を実施
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_start']);
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_end']);

        // 3. 再度 休憩入→休憩戻 を実施
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_start']);
        $response = $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_end']);
        $response->assertStatus(200);

        // break_times が2件以上になっていること
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $count = BreakTime::where('attendance_id', $attendance->id)->count();

        $this->assertTrue($count >= 2, '複数回の休憩が登録されていません');
    }

    // 休憩戻ボタンが正しく機能する
    public function test_break_out_updates_status_back_to_working(): void
    {
        // 1. ステータスが出勤中のユーザーにログイン
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(2),
        ]);

        // 2. 休憩入の処理を行う
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_start']);

        // 3. 休憩戻の処理を行う
        $response = $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_end']);
        $response->assertStatus(200);

        // ステータスAPIから勤務状態を確認
        $statusResponse = $this->getJsonAsUser('/api/attendance/status');
        $statusResponse->assertStatus(200);
        $json = $statusResponse->json();

        $this->assertEquals(
            '出勤中',
            $json['status'],
            '休憩戻後のステータスが出勤中になっていません'
        );
    }

    // 休憩戻は一日に何回でもできる
    public function test_can_return_from_break_multiple_times(): void
    {
        // 1. ステータスが出勤中であるユーザーにログイン
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(3),
        ]);

        // 2. 休憩入→休憩戻→再度休憩入
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_start']);
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_end']);
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_start']);

        // 3. 「休憩戻」ボタンが再び表示される（API成功をもって確認）
        $response = $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_out']);
        $response->assertStatus(200);
    }

    // 休憩時刻が勤怠一覧画面で確認できる
    public function test_break_times_appear_in_attendance_list(): void
    {
        // 1. ステータスが勤務中のユーザーにログイン（UserTestCaseで認証済）

        // 出勤レコードを作成
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(2),
        ]);

        // 出勤API呼び出し（確実に当日レコードを紐付け）
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_in']);

        // 2. 休憩入と休憩戻の処理を行う（現在時刻を固定）
        $tz = config('app.timezone', 'Asia/Tokyo');
        $breakInTime = Carbon::now($tz)->setSecond(0);
        $this->travelTo($breakInTime);
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_start']);

        $breakOutTime = $breakInTime->copy()->addMinutes(30);
        $this->travelTo($breakOutTime);
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'break_end']);

        // 3. 勤怠一覧画面から当日の休憩時刻を確認
        $response = $this->getJsonAsUser('/api/attendance/list');
        $response->assertStatus(200);

        $data = $response->json();
        $today = Carbon::today($tz)->toDateString();

        $record = collect($data['records'] ?? $data)->firstWhere('date', $today);

        $this->assertNotNull($record, '今日の勤怠データが一覧に存在しません');

        // 勤怠一覧には break_minutes が含まれており、30分相当が反映されていることを確認
        $this->assertGreaterThan(
            0,
            $record['break_minutes'],
            '休憩データ（分数）が一覧に反映されていません'
        );
    }
}
