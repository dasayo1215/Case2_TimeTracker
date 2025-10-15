<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'date' => ['required', 'date'],
            'clock_in' => ['required', 'date_format:H:i:s'],
            'clock_out' => ['required', 'date_format:H:i:s'],
            'breakTimes.*.break_start' => ['nullable', 'date_format:H:i:s'],
            'breakTimes.*.break_end'   => ['nullable', 'date_format:H:i:s'],
            'remarks' => ['required', 'string'],
        ];

        // 管理者ルート（/api/admin/...）の場合のみ user_id を必須にする
        if ($this->is('api/admin/*')) {
            $rules['user_id'] = ['required', 'integer', 'exists:users,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // 出退勤
            'clock_in.required'     => '出勤時間を入力してください',
            'clock_out.required'    => '退勤時間を入力してください',
            'clock_in.date_format'  => '出勤時間の形式が不正です',
            'clock_out.date_format' => '退勤時間の形式が不正です',

            // 休憩
            'breakTimes.*.break_start.date_format' => '休憩時間の形式が不正です',
            'breakTimes.*.break_end.date_format'   => '休憩時間の形式が不正です',

            // 備考
            'remarks.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks   = $this->input('breakTimes', []);

            // --- メッセージを一時的に集約 ---
            $messages = [
                'clock_in'   => [],
                'breakTimes' => [],
                'remarks'    => [],
            ];

            // --- 1. 出勤時間 > 退勤時間（ゼロはOKにする） ---
            if ($clockIn && $clockOut && strtotime($clockIn) > strtotime($clockOut)) {
                $messages['clock_in'][] = '出勤時間もしくは退勤時間が不適切な値です';
            }

            // --- 2. 休憩開始が 出勤より前 or 退勤より後（イコールはOK） ---
            foreach ($breaks as $b) {
                if (!empty($b['break_start'])) {
                    if ($clockIn && strtotime($b['break_start']) < strtotime($clockIn)) {
                        $messages['breakTimes'][] = '休憩時間が不適切な値です';
                    }
                    if ($clockOut && strtotime($b['break_start']) > strtotime($clockOut)) {
                        $messages['breakTimes'][] = '休憩時間が不適切な値です';
                    }
                }
            }

            // --- 3. 休憩終了が退勤より後（イコールはOK） ---
            foreach ($breaks as $b) {
                if (!empty($b['break_end']) && $clockOut && strtotime($b['break_end']) > strtotime($clockOut)) {
                    $messages['breakTimes'][] = '休憩時間もしくは退勤時間が不適切な値です';
                }
            }

            // --- 4. 休憩開始 > 休憩終了（逆転チェック） ---
            foreach ($breaks as $b) {
                if (!empty($b['break_start']) && !empty($b['break_end'])) {
                    if (strtotime($b['break_start']) > strtotime($b['break_end'])) {
                        $messages['breakTimes'][] = '休憩時間が不適切な値です';
                    }
                }
            }

            // --- 5. 休憩時間同士の重複チェック ---
            for ($i = 0; $i < count($breaks); $i++) {
                $b1 = $breaks[$i];
                if (empty($b1['break_start']) || empty($b1['break_end'])) continue;

                for ($j = $i + 1; $j < count($breaks); $j++) {
                    $b2 = $breaks[$j];
                    if (empty($b2['break_start']) || empty($b2['break_end'])) continue;

                    $start1 = strtotime($b1['break_start']);
                    $end1   = strtotime($b1['break_end']);
                    $start2 = strtotime($b2['break_start']);
                    $end2   = strtotime($b2['break_end']);

                    if ($start1 < $end2 && $start2 < $end1) {
                        $messages['breakTimes'][] = '休憩時間が重複しています';
                    }
                }
            }

            // --- 6. 備考未入力 ---
            if (!$this->filled('remarks')) {
                $messages['remarks'][] = '備考を記入してください';
            }

            // --- 最後に重複メッセージを1つずつ登録 ---
            foreach ($messages as $field => $msgs) {
                foreach (array_unique($msgs) as $msg) {
                    $validator->errors()->add($field, $msg);
                }
            }
        });
    }
}
