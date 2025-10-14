<?php

namespace Tests\Feature\Admin\Staff;

use Tests\Feature\Admin\AdminTestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ApprovalTest extends AdminTestCase
{
    // 承認待ちの修正申請が全て表示されている
    public function test_pending_correction_requests_are_listed(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーと勤怠データ（承認待ち）を作成
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $attendance1 = Attendance::factory()->create([
            'user_id'      => $user1->id,
            'work_date'    => Carbon::today()->toDateString(),
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => '修正申請中1',
            'status'       => 'pending',
            'submitted_at' => now(),
            'approved_at'  => null,
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id'      => $user2->id,
            'work_date'    => Carbon::yesterday()->toDateString(),
            'clock_in'     => '08:30:00',
            'clock_out'    => '17:30:00',
            'remarks'      => '修正申請中2',
            'status'       => 'pending',
            'submitted_at' => now(),
            'approved_at'  => null,
        ]);

        // 2. 修正申請一覧ページを開き、承認待ちのタブを開く
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/attendance/requests?status=pending');
        $response->assertStatus(200);

        $records = $response->json('records') ?? [];

        // 全ユーザーの未承認の修正申請が表示される
        $this->assertCount(2, $records, '承認待ち申請が全件取得できていません');
        $this->assertTrue(collect($records)->every(fn($r) => $r['status'] === 'pending'));
    }

    // 承認済みの修正申請が全て表示されている
    public function test_approved_correction_requests_are_listed(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーと勤怠データ（承認済み）を作成
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        Attendance::factory()->create([
            'user_id'      => $user1->id,
            'work_date'    => Carbon::today()->toDateString(),
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => '承認済み1',
            'status'       => 'approved',
            'submitted_at' => now()->subDay(),
            'approved_at'  => now(),
        ]);

        Attendance::factory()->create([
            'user_id'      => $user2->id,
            'work_date'    => Carbon::yesterday()->toDateString(),
            'clock_in'     => '08:30:00',
            'clock_out'    => '17:30:00',
            'remarks'      => '承認済み2',
            'status'       => 'approved',
            'submitted_at' => now()->subDays(2),
            'approved_at'  => now()->subDay(),
        ]);

        // 2. 修正申請一覧ページを開き、承認済みのタブを開く
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/attendance/requests?status=approved');
        $response->assertStatus(200);

        $records = $response->json('records') ?? [];

        // 全ユーザーの承認済みの修正申請が表示される
        $this->assertCount(2, $records, '承認済み申請が全件取得できていません');
        $this->assertTrue(collect($records)->every(fn($r) => $r['status'] === 'approved'));
    }

    // 修正申請の詳細内容が正しく表示されている
    public function test_correction_request_detail_is_displayed_correctly(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーと勤怠データを作成
        $user = User::factory()->create(['name' => 'テスト太郎', 'role' => 'user']);

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => '2025-10-14',
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => '修正申請中',
            'status'       => 'pending',
            'submitted_at' => now(),
            'approved_at'  => null,
        ]);

        // 2. 修正申請の詳細画面を開く
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/api/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $data = $response->json();

        // 申請内容が正しく表示されている
        $this->assertEquals('テスト太郎', $data['user_name']);
        $this->assertEquals('2025-10-14', $data['date']);
        $this->assertEquals('09:00:00', $data['clock_in']);
        $this->assertEquals('18:00:00', $data['clock_out']);
        $this->assertEquals('修正申請中', $data['remarks']);
        $this->assertEquals('pending', $data['status']);
    }

    // 修正申請の承認処理が正しく行われる
    public function test_correction_request_is_approved_and_attendance_updated(): void
    {
        // 1. 管理者ユーザーにログインをする（AdminTestCaseでログイン済）
        $this->assertAuthenticatedAs($this->admin, 'admin');

        // 一般ユーザーと勤怠データを作成
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => Carbon::today()->toDateString(),
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'remarks'      => '修正申請中',
            'status'       => 'pending',
            'submitted_at' => now(),
            'approved_at'  => null,
        ]);

        // 2. 修正申請の詳細画面で「承認」ボタンを押す
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/api/admin/attendance/approve/' . $attendance->id);
        $response->assertStatus(200);

        // 修正申請が承認され、勤怠情報が更新される
        $this->assertDatabaseHas('attendances', [
            'id'         => $attendance->id,
            'status'     => 'approved',
        ]);
    }
}
