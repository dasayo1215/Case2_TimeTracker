<?php

namespace Tests\Feature\User\Attendance;

use Tests\Feature\User\UserTestCase;
use App\Models\Attendance;
use Carbon\Carbon;

class ListTest extends UserTestCase
{
    // 自分が行った勤怠情報が全て表示されている
    public function test_user_can_view_all_own_attendance_records(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログイン（UserTestCaseで認証済）

        // 自分の勤怠レコードを3日分作成
        collect(range(0, 2))->each(function ($i) {
            Attendance::factory()->create([
                'user_id'   => $this->user->id,
                'work_date' => Carbon::today()->subDays($i)->toDateString(),
                'clock_in'  => '09:00:00',
                'clock_out' => '18:00:00',
            ]);
        });

        // 他ユーザーの勤怠レコード（混在確認用）
        Attendance::factory()->create([
            'user_id'   => \App\Models\User::factory()->create()->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        // 2. 勤怠一覧ページを開く
        $response = $this->getJsonAsUser('/api/attendance/list');
        $response->assertStatus(200);

        // 3. 自分の勤怠情報のみが表示されていることを確認
        $data = $response->json();
        $records = collect($data['records'] ?? $data);

        $this->assertTrue(
            $records->every(fn ($r) => $r['user_id'] === $this->user->id),
            '他ユーザーの勤怠データが一覧に含まれています'
        );

        $this->assertGreaterThanOrEqual(3, $records->count(), '勤怠レコードが全て表示されていません');
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_current_month_is_displayed_by_default(): void
    {
        // 1. ユーザーにログイン（UserTestCaseで認証済）

        // 2. 勤怠一覧ページを開く
        $response = $this->getJsonAsUser('/api/attendance/list');
        $response->assertStatus(200);

        // 3. 現在の月が返却データに含まれていることを確認
        $data = $response->json();
        $month = $data['month'] ?? null;
        $expectedMonth = Carbon::now()->format('Y/m');

        $this->assertEquals($expectedMonth, $month, '現在の月が正しく表示されていません');
    }

    // 「前月」を押下した時に前月の情報が表示される
    public function test_previous_month_records_are_displayed(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログイン
        $prevMonth = Carbon::now()->subMonth();
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => $prevMonth->copy()->day(10)->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 2. 勤怠一覧ページを開く（前月指定）
        $param = '?month=' . $prevMonth->format('Y-m');
        $response = $this->getJsonAsUser('/api/attendance/list' . $param);
        $response->assertStatus(200);

        // 3. 前月の勤怠情報が表示されていることを確認
        $data = $response->json();
        $records = collect($data['records'] ?? $data);

        $this->assertTrue(
            $records->every(fn ($r) => str_starts_with($r['date'], $prevMonth->format('Y-m'))),
            '前月の勤怠情報が表示されていません'
        );
    }

    // 「翌月」を押下した時に翌月の情報が表示される
    public function test_next_month_records_are_displayed(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログイン
        $nextMonth = Carbon::now()->addMonth();
        Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => $nextMonth->copy()->day(5)->toDateString(),
            'clock_in'  => '09:30:00',
            'clock_out' => '18:30:00',
        ]);

        // 2. 勤怠一覧ページを開く（翌月指定）
        $param = '?month=' . $nextMonth->format('Y-m');
        $response = $this->getJsonAsUser('/api/attendance/list' . $param);
        $response->assertStatus(200);

        // 3. 翌月の勤怠情報が表示されていることを確認
        $data = $response->json();
        $records = collect($data['records'] ?? $data);

        $this->assertTrue(
            $records->every(fn ($r) => str_starts_with($r['date'], $nextMonth->format('Y-m'))),
            '翌月の勤怠情報が表示されていません'
        );
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_clicking_detail_redirects_to_attendance_detail(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログイン
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 2. 勤怠一覧ページを開く
        $response = $this->getJsonAsUser('/api/attendance/list');
        $response->assertStatus(200);

        // 3. 「詳細」ボタン押下を想定して該当勤怠の詳細を取得
        $detailResponse = $this->getJsonAsUser('/api/attendance/detail/' . $attendance->id);
        $detailResponse->assertStatus(200);

        $data = $detailResponse->json();
        $record = $data['record'] ?? $data;
        $this->assertEquals(
            $attendance->id,
            $record['id'],
            '勤怠詳細画面が正しいレコードを返していません'
        );
    }
}
