<?php

namespace Tests\Feature\Admin\Staff;

use Tests\Feature\Admin\AdminTestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ListTest extends AdminTestCase
{
    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_admin_can_view_all_users_with_name_and_email(): void
    {
        // 1. 管理者でログインする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーを3名作成
        $users = User::factory()->count(3)->create(['role' => 'user']);

        // 2. スタッフ一覧ページを開く
        $response = $this->actingAs($this->admin, 'admin')->getJson('/api/admin/staff/list');
        $response->assertStatus(200);

        $records = $response->json('records') ?? $response->json();

        // すべての一般ユーザーの氏名・メールが含まれていることを確認
        foreach ($users as $user) {
            $this->assertTrue(
                collect($records)->contains(fn ($r) =>
                    ($r['name'] ?? null) === $user->name &&
                    ($r['email'] ?? null) === $user->email
                ),
                "ユーザー {$user->name} の情報が一覧に存在しません"
            );
        }
    }

    // ユーザーの勤怠情報が正しく表示される
    public function test_admin_can_view_selected_user_attendance_list(): void
    {
        // 1. 管理者ユーザーでログインする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 対象の一般ユーザーと勤怠データを複数作成（別日付）
        $user = User::factory()->create(['role' => 'user']);
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $attendances = [
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $today,
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]),
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $yesterday,
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
            ]),
        ];

        // 2. 選択したユーザーの勤怠一覧ページを開く
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/api/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);

        $records = $response->json('records') ?? [];

        // 勤怠情報が正確に取得できていることを確認
        foreach ($attendances as $a) {
            $this->assertTrue(
                collect($records)->contains(fn ($r) =>
                    ($r['date'] ?? null) === $a->work_date &&
                    ($r['clock_in'] ?? null) === $a->clock_in &&
                    ($r['clock_out'] ?? null) === $a->clock_out
                ),
                "日付 {$a->work_date} の勤怠情報が正しく取得されていません"
            );
        }
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_previous_month_button_shows_previous_month_data(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        $user = User::factory()->create(['role' => 'user']);
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');
        $workDate = "{$previousMonth}-10";

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 2. 勤怠一覧ページを開く
        // 3. 「前月」ボタンを押す
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/api/admin/attendance/staff/{$user->id}?month={$previousMonth}");
        $response->assertStatus(200);

        $records = $response->json('records') ?? [];

        $this->assertTrue(
            collect($records)->contains(fn ($r) =>
                ($r['date'] ?? null) === $workDate
            ),
            "前月データ（{$workDate}）が表示されていません"
        );
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_next_month_button_shows_next_month_data(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        $user = User::factory()->create(['role' => 'user']);
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        $workDate = "{$nextMonth}-05";

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        // 2. 勤怠一覧ページを開く
        // 3. 「翌月」ボタンを押す
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/api/admin/attendance/staff/{$user->id}?month={$nextMonth}");
        $response->assertStatus(200);

        $records = $response->json('records') ?? [];

        $this->assertTrue(
            collect($records)->contains(fn ($r) =>
                ($r['date'] ?? null) === $workDate
            ),
            "翌月データ（{$workDate}）が表示されていません"
        );
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_detail_button_navigates_to_attendance_detail(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 2. 勤怠一覧ページを開く
        // 3. 「詳細」ボタンを押下する
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/api/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $data = $response->json();

        // その日の勤怠詳細画面に遷移し、データが一致していることを確認
        $this->assertEquals($user->id, $data['user_id'] ?? null);
        $this->assertEquals($attendance->work_date, $data['date'] ?? null);
        $this->assertEquals($attendance->clock_in, $data['clock_in'] ?? null);
        $this->assertEquals($attendance->clock_out, $data['clock_out'] ?? null);
    }
}
