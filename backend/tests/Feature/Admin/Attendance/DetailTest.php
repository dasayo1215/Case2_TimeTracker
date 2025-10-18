<?php

namespace Tests\Feature\Admin\Attendance;

use Tests\Feature\Admin\AdminTestCase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class DetailTest extends AdminTestCase
{
    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_detail_page_shows_selected_attendance_data(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        $targetUser = User::factory()->create(['name' => 'テスト太郎', 'role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id'      => $targetUser->id,
            'work_date'    => '2025-10-14',
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => null,
            'status'       => 'normal',
            'submitted_at' => null,
            'approved_at'  => null,
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('テスト太郎', $data['user_name']);
        $this->assertEquals($targetUser->id, $data['user_id']);
        $this->assertEquals('2025-10-14', $data['date']);
        $this->assertEquals('09:00:00', $data['clock_in']);
        $this->assertEquals('18:00:00', $data['clock_out']);
        $this->assertNull($data['remarks']);
        $this->assertEquals('normal', $data['status']);
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_after_clock_out_shows_error(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        $targetUser = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id'      => $targetUser->id,
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => null,
            'status'       => 'normal',
            'submitted_at' => null,
            'approved_at'  => null,
        ]);

        // 3. 出勤時間を退勤時間より後に設定
        $payload = [
            'clock_in'  => '19:00:00',
            'clock_out' => '18:00:00',
            'remarks'   => '修正テスト',
        ];

        // 2,4. 勤怠詳細ページで保存処理をする
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admin/attendance/update-or-create/', $payload);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['clock_in']);
        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            $response->json('errors.clock_in.0') ?? ''
        );
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_start_after_clock_out_shows_error(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        $targetUser = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id'      => $targetUser->id,
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => null,
            'status'       => 'normal',
            'submitted_at' => null,
            'approved_at'  => null,
        ]);

        // 3. 休憩開始を退勤時間より後に設定
        $payload = [
            'date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'breakTimes' => [
                ['break_start' => '19:00:00', 'break_end' => '19:30:00'],
            ],
            'remarks' => '修正テスト',
        ];

        // 2,4. 勤怠詳細ページで保存処理をする
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admin/attendance/update-or-create/', $payload);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['breakTimes']);
        $this->assertStringContainsString(
            '休憩時間が不適切な値です',
            implode('', $response->json('errors.breakTimes') ?? [])
        );
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_end_after_clock_out_shows_error(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        $targetUser = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id'      => $targetUser->id,
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => null,
            'status'       => 'normal',
            'submitted_at' => null,
            'approved_at'  => null,
        ]);

        // 3. 休憩終了を退勤時間より後に設定
        $payload = [
            'date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'breakTimes' => [
                ['break_start' => '17:00:00', 'break_end' => '19:00:00'],
            ],
            'remarks' => '修正テスト',
        ];

        // 2,4. 勤怠詳細ページで保存処理をする
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admin/attendance/update-or-create/', $payload);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['breakTimes']);
        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            implode('', $response->json('errors.breakTimes') ?? [])
        );
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_remarks_required_error(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        $targetUser = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id'      => $targetUser->id,
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => null,
            'status'       => 'normal',
            'submitted_at' => null,
            'approved_at'  => null,
        ]);

        // 3. 備考欄を未入力のまま保存処理をする
        $payload = [
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remarks'   => '',
        ];

        // 2,4. 勤怠詳細ページで保存処理をする
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admin/attendance/update-or-create/', $payload);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['remarks']);
        $this->assertStringContainsString(
            '備考を記入してください',
            $response->json('errors.remarks.0') ?? ''
        );
    }
}
