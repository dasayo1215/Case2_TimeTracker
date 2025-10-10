<?php

namespace Tests\Feature\User\Attendance;

use Tests\Feature\User\UserTestCase;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockInTest extends UserTestCase
{
    // 出勤ボタンが正しく機能する
    public function test_clock_in_creates_record_and_changes_status(): void
    {
        // 1. ステータスが勤務外のユーザーにログイン（UserTestCaseで認証済）

        // 2. 画面に「出勤」ボタンが表示されていることを確認する
        // ※ APIテストでは実際のUI確認はできないため、勤務外状態＝出勤可能であることを前提に進める

        // 3. 出勤の処理を行う
        $response = $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_in']);
        $response->assertStatus(200);

        // 出勤レコードが作成されていること
        $this->assertDatabaseHas('attendances', [
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);
    }

    // 出勤は一日一回のみできる
    public function test_cannot_clock_in_twice_in_same_day(): void
    {
        // 1. ステータスが退勤済のユーザーを作成
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now()->subHour(),
        ]);

        // 2. 出勤ボタンが表示されないことを確認する
        // ※ UI確認は不可。代わりにAPIで打刻が拒否されることを確認
        $response = $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_in']);

        // 3. 出勤できないことを確認
        $response->assertStatus(400);
    }

    // 出勤時刻が勤怠一覧画面で確認できる
    public function test_clock_in_time_appears_in_attendance_list(): void
    {
        // 1. ステータスが勤務外のユーザーにログイン（UserTestCaseで認証済）
        // 2. 出勤処理を行う（現在時刻を基準に打刻）
        $tz = config('app.timezone', 'Asia/Tokyo');
        $clockInTime = Carbon::now($tz)->setSecond(0);
        $this->travelTo($clockInTime);
        $this->postJsonAsUser('/api/attendance/clock', ['action' => 'clock_in']);

        // 3. 勤怠一覧画面から当日の出勤時刻を確認
        $response = $this->getJsonAsUser('/api/attendance/list');
        $response->assertStatus(200);

        $data = $response->json();
        $today = Carbon::today($tz)->toDateString();

        $found = collect($data['records'] ?? $data)->firstWhere('date', $today);

        $this->assertNotNull($found, '今日の勤怠データが一覧に存在しません');

        // 出勤時刻が正確に記録されていることを確認
        $this->assertEquals(
            $clockInTime->format('H:i'),
            substr($found['clock_in'], 0, 5),
            '出勤時刻が正確に記録されていません'
        );
    }
}
