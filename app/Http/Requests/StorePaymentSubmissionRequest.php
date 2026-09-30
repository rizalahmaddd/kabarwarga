<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentSubmissionRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'household_id' => ['required', Rule::exists('households', 'id')->where('is_active', true)],
            'dues_type_id' => ['required', Rule::exists('dues_types', 'id')->where('is_active', true)],
            'periods' => ['nullable', 'array', 'max:24'],
            'periods.*' => ['distinct', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'bank_account_id' => ['required', Rule::exists('bank_accounts', 'id')->where('is_active', true)],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic,pdf', 'max:5120'],
            'payer_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bank_account_id.required' => 'Pilih rekening tujuan yang Anda transfer.',
            'proof.required' => 'Lampirkan foto atau screenshot bukti transfer.',
            'proof.mimes' => 'Bukti harus berupa foto (JPG/PNG/WEBP) atau PDF.',
            'proof.max' => 'Ukuran file bukti maksimal 5 MB.',
        ];
    }
}
