<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'remarks' => 'required|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            // ① 出勤 ≥ 退勤
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // ② 休憩の論理チェック
            $restStarts = $this->input('rest_start', []);
            $restEnds   = $this->input('rest_end', []);

            foreach ($restStarts as $index => $restStart) {
                $restEnd = $restEnds[$index] ?? null;

                if ($restStart && $clockIn && $clockOut) {
                    // 休憩開始が出勤前または退勤後
                    if ($restStart < $clockIn || $restStart > $clockOut) {
                        $validator->errors()->add('rest_start', '休憩時間が不適切な値です');
                    }
                }

                // ③ 休憩終了が退勤より後
                if ($restEnd && $clockOut && $restEnd > $clockOut) {
                    $validator->errors()->add('rest_end', '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'remarks.required' => '備考を記入してください',
        ];
    }
}
