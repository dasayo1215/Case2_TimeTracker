<?php

namespace Tests\Feature\User\Attendance;

use Tests\Feature\User\UserTestCase;
use App\Models\Attendance;
use Carbon\Carbon;

class CorrectionValidationTest extends UserTestCase
{
    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_after_clock_out_shows_error(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remarks'   => null,
            'status'    => 'normal',
            'submitted_at' => null,
            'approved_at' =>null,
        ]);

        // 2. 勤怠詳細ページを開く（＝対象データを指定）
        // 3. 出勤時間を退勤時間より後に設定する
        $payload = [
            'clock_in'  => '19:00:00',
            'clock_out' => '18:00:00',
            'remarks'   => 'テスト修正',
        ];

        // 4. 保存処理をする
        $response = $this->postJsonAsUser('/api/attendance/update-or-create/', $payload);
        $response->assertStatus(422);

        // 「出勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertJsonValidationErrors(['clock_in']);
        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            $response->json('errors.clock_in.0') ?? '',
            '「出勤時間もしくは退勤時間が不適切な値です」というエラーメッセージが表示されていません'
        );
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_start_after_clock_out_shows_error(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remarks'   => null,
            'status'    => 'normal',
            'submitted_at' => null,
            'approved_at' =>null,
        ]);

        // 2. 勤怠詳細ページを開く → 3. 休憩開始を退勤時間より後に設定する
        $payload = [
            'date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'breakTimes' => [
                ['break_start' => '19:00:00', 'break_end' => '19:30:00'],
            ],
            'remarks' => 'テスト修正',
        ];

        // 4. 保存処理をする
        $response = $this->postJsonAsUser('/api/attendance/update-or-create/', $payload);
        $response->assertStatus(422);

        // 「休憩時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertJsonValidationErrors(['breakTimes']);
        $this->assertStringContainsString(
            '休憩時間が不適切な値です',
            implode('', $response->json('errors.breakTimes') ?? []),
            '「休憩時間が不適切な値です」というエラーメッセージが表示されていません'
        );
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_end_after_clock_out_shows_error(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remarks'   => null,
            'status'    => 'normal',
            'submitted_at' => null,
            'approved_at' =>null,
        ]);

        // 2. 勤怠詳細ページを開く → 3. 休憩終了を退勤時間より後に設定する
        $payload = [
            'date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'breakTimes' => [
                ['break_start' => '17:00:00', 'break_end' => '19:00:00'],
            ],
            'remarks' => 'テスト修正',
        ];

        // 4. 保存処理をする
        $response = $this->postJsonAsUser('/api/attendance/update-or-create/', $payload);
        $response->assertStatus(422);

        // 「休憩時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertJsonValidationErrors(['breakTimes']);
        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            implode('', $response->json('errors.breakTimes') ?? []),
            '「休憩時間もしくは退勤時間が不適切な値です」というエラーメッセージが表示されていません'
        );
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_remarks_required_error(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remarks'   => null,
            'status'    => 'normal',
            'submitted_at' => null,
            'approved_at' =>null,
        ]);

        // 2. 勤怠詳細ページを開く → 3. 備考欄を未入力にする
        $payload = [
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'remarks'   => '',
        ];

        // 4. 保存処理をする
        $response = $this->postJsonAsUser('/api/attendance/update-or-create/', $payload);
        $response->assertStatus(422);

        // 「備考を記入してください」というバリデーションメッセージが表示される
        $response->assertJsonValidationErrors(['remarks']);
        $this->assertStringContainsString(
            '備考を記入してください',
            $response->json('errors.remarks.0') ?? '',
            '「備考を記入してください」というエラーメッセージが表示されていません'
        );
    }
}
