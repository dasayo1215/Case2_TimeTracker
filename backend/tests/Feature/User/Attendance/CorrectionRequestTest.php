<?php

namespace Tests\Feature\User\Attendance;

use Tests\Feature\User\UserTestCase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class CorrectionRequestTest extends UserTestCase
{
    // 修正申請処理が実行される（管理者画面に表示される）
    public function test_correction_request_is_created_and_visible_to_admin(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $attendance = Attendance::factory()->create([
            'user_id'     => $this->user->id,
            'work_date'   => Carbon::today()->toDateString(),
            'clock_in'    => '09:00:00',
            'clock_out'   => '18:00:00',
            'remarks'     => null,
            'status'      => 'normal',
            'submitted_at'=> null,
            'approved_at' => null,
        ]);

        // 2. 勤怠詳細を修正し保存処理をする
        $payload = [
            'date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:30:00',
            'clock_out' => '18:15:00',
            'remarks'   => '修正申請テスト',
        ];
        $response = $this->postJsonAsUser('/api/attendance/update-or-create/', $payload);
        $response->assertStatus(200);

        // 3-1. 管理者ユーザーで申請一覧画面を確認する
        $admin = User::factory()->create(['role' => 'admin']);
        $adminResponse = $this->actingAs($admin, 'admin')->getJson('/api/admin/attendance/requests');
        $adminResponse->assertStatus(200);
        $requestRecords = $adminResponse->json('records') ?? [];

        $this->assertTrue(
            collect($requestRecords)->contains(fn ($r) => $r['user_id'] === $this->user->id),
            '修正申請が管理者の申請一覧画面に表示されていません'
        );

        // 3-2. 管理者ユーザーで承認画面を確認
        $detailResponse = $this->actingAs($admin, 'admin')->getJson('/api/admin/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);

        $data = $detailResponse->json();
        $this->assertEquals(
            $this->user->id,
            $data['user_id'] ?? $data['user']['id'] ?? null,
            '修正申請が管理者の承認画面で正しく取得できていません'
        );
    }

    // 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_pending_requests_visible_to_user(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $attendance = Attendance::factory()->create([
            'user_id'     => $this->user->id,
            'work_date'   => Carbon::today()->toDateString(),
            'clock_in'    => '09:00:00',
            'clock_out'   => '18:00:00',
            'remarks'     => null,
            'status'      => 'normal',
            'submitted_at'=> null,
            'approved_at' => null,
        ]);

        // 2. 勤怠詳細を修正し保存処理をする
        $payload = [
            'date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:10:00',
            'clock_out' => '18:10:00',
            'remarks'   => '申請テスト',
        ];
        $this->postJsonAsUser('/api/attendance/update-or-create/', $payload);

        // 3. 申請一覧画面を確認する
        $response = $this->getJsonAsUser('/api/attendance/requests?status=pending');
        $response->assertStatus(200);
        $records = $response->json('records') ?? [];

        $this->assertTrue(
            collect($records)->contains(fn ($r) => $r['user_id'] === $this->user->id),
            '承認待ち一覧に自分の申請が表示されていません'
        );
    }

    // 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_approved_requests_visible_to_user(): void
{
    // 1. 勤怠情報が登録されたユーザーにログインをする
    $attendance = Attendance::factory()->create([
        'user_id'     => $this->user->id,
        'work_date'   => Carbon::today()->toDateString(),
        'clock_in'    => '09:00:00',
        'clock_out'   => '18:00:00',
        'remarks'     => null,
        'status'      => 'normal',
        'submitted_at'=> null,
        'approved_at' => null,
    ]);

    // 2. 勤怠詳細を修正し保存処理をする
    $payload = [
        'date'       => Carbon::today()->toDateString(),
        'clock_in'   => '09:15:00',
        'clock_out'  => '18:20:00',
        'remarks'    => '承認申請テスト',
    ];
    $this->postJsonAsUser('/api/attendance/update-or-create/', $payload);

    // 管理者が承認処理を実行
    $admin = User::factory()->create(['role' => 'admin']);
    $approveResponse = $this->actingAs($admin, 'admin')
        ->postJson('/api/admin/attendance/approve/' . $attendance->id);
    $approveResponse->assertStatus(200);

    // 3. 一般ユーザーで申請一覧画面を開く（承認済み）
    $response = $this->getJsonAsUser('/api/attendance/requests?status=approved');
    $response->assertStatus(200);

    // 4. 管理者が承認した修正申請が全て表示されていることを確認
    $records = $response->json('records') ?? [];

    $this->assertTrue(
        collect($records)->contains(fn ($r) => $r['user_id'] === $this->user->id && $r['status'] === 'approved'),
        '承認済み一覧に管理者が承認した申請が表示されていません'
    );
}

    // 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
    public function test_request_detail_link_opens_attendance_detail(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $attendance = Attendance::factory()->create([
            'user_id'     => $this->user->id,
            'work_date'   => Carbon::today()->toDateString(),
            'clock_in'    => '09:00:00',
            'clock_out'   => '18:00:00',
            'remarks'     => null,
            'status'      => 'normal',
            'submitted_at'=> null,
            'approved_at' => null,
        ]);

        // 2. 勤怠詳細を修正し保存処理をする
        $payload = [
            'date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:30:00',
            'clock_out' => '18:15:00',
            'remarks'   => '詳細確認用',
        ];
        $this->postJsonAsUser('/api/attendance/update-or-create/', $payload);

        // 3. 申請一覧画面を開く → 4. 「詳細」ボタンを押す（勤怠詳細ページを開く想定）
        $response = $this->getJsonAsUser('/api/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(
            $attendance->id,
            $data['record']['id'] ?? $data['id'] ?? null,
            '申請詳細から勤怠詳細画面に正しく遷移できていません'
        );
    }
}
