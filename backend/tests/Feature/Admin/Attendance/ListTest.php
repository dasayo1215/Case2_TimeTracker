<?php

namespace Tests\Feature\Admin\Attendance;

use Tests\Feature\Admin\AdminTestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ListTest extends AdminTestCase
{
    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_admin_can_view_all_users_attendance_for_today(): void
    {
        // 1. 管理者ユーザーにログインする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーを2人作成
        $users = User::factory()->count(2)->create(['role' => 'user']);
        $today = Carbon::today()->toDateString();

        // 各ユーザーの勤怠データを登録
        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id'   => $user->id,
                'work_date' => $today,
                'clock_in'  => '09:00:00',
                'clock_out' => '18:00:00',
            ]);
        }

        // 2. 勤怠一覧画面を開く
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/attendance/list?date=' . $today);
        $response->assertStatus(200);

        $records = $response->json();

        // 一覧に全ユーザー分の勤怠が含まれていることを確認
        foreach ($users as $user) {
            $record = collect($records)->first(fn ($r) =>
                ($r['user']['id'] ?? null) === $user->id
            );

            $this->assertNotNull($record, "ユーザーID {$user->id} の勤怠情報が一覧に含まれていません");
            $this->assertEquals('09:00:00', $record['clock_in']);
            $this->assertEquals('18:00:00', $record['clock_out']);
        }
    }

    // 遷移した際に現在の日付が表示される
    public function test_attendance_list_shows_current_date_by_default(): void
    {
        // 1. 管理者ユーザーにログインする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーを作成
        $user = User::factory()->create(['role' => 'user']);
        $today = Carbon::today()->toDateString();

        // 当日の勤怠データを登録
        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $today,
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 2. 勤怠一覧を開く
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/attendance/list');
        $response->assertStatus(200);

        $records = $response->json();

        // 4. 今日の日付のデータが含まれていることを確認
        $this->assertTrue(
            collect($records)->contains(fn ($r) =>
                ($r['user']['id'] ?? null) === $user->id &&
                $r['clock_in'] === '09:00:00' &&
                $r['clock_out'] === '18:00:00'
            ),
            '勤怠一覧に今日の勤怠データが表示されていません'
        );
    }

    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function test_previous_day_button_shows_previous_day_data(): void
    {
        // 1. 管理者ユーザーにログインする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーを作成
        $user = User::factory()->create(['role' => 'user']);
        $yesterday = Carbon::yesterday()->toDateString();

        // 前日の勤怠データを登録
        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $yesterday,
            'clock_in'  => '09:30:00',
            'clock_out' => '18:30:00',
        ]);

        // 2. 勤怠一覧画面を開く
        // 3. 「前日」ボタンを押す
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/attendance/list?date=' . $yesterday);
        $response->assertStatus(200);

        $records = $response->json();

        // 正しい勤怠データが取得できていることを確認
        $record = collect($records)->first(fn ($r) =>
            ($r['user']['id'] ?? null) === $user->id
        );

        $this->assertNotNull($record, '前日データが一覧に存在しません');
        $this->assertEquals('09:30:00', $record['clock_in']);
        $this->assertEquals('18:30:00', $record['clock_out']);
        if (isset($record['work_date'])) {
            $this->assertEquals($yesterday, $record['work_date'], '前日の日付が正しくありません');
        }
    }

    // 「翌日」を押下した時に次の日の勤怠情報が表示される
    public function test_next_day_button_shows_next_day_data(): void
    {
        // 1. 管理者ユーザーにログインする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーを作成
        $user = User::factory()->create(['role' => 'user']);
        $tomorrow = Carbon::tomorrow()->toDateString();

        // 翌日の勤怠データを登録
        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $tomorrow,
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        // 2. 勤怠一覧画面を開く
        // 3. 「翌日」ボタンを押す
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/attendance/list?date=' . $tomorrow);
        $response->assertStatus(200);

        $records = $response->json();

        // 4. 正しい勤怠データが取得できていることを確認
        $record = collect($records)->first(fn ($r) =>
            ($r['user']['id'] ?? null) === $user->id
        );

        $this->assertNotNull($record, '翌日データが一覧に存在しません');
        $this->assertEquals('10:00:00', $record['clock_in']);
        $this->assertEquals('19:00:00', $record['clock_out']);
        if (isset($record['work_date'])) {
            $this->assertEquals($tomorrow, $record['work_date'], '翌日の日付が正しくありません');
        }
    }
}
