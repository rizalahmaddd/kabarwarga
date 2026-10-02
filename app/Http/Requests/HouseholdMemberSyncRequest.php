<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class HouseholdMemberSyncRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kk_number' => ['nullable', 'string', 'max:30'],
            'occupancy_status' => ['nullable', 'in:pemilik,kontrak'],
            'sync_head_name' => ['nullable', 'boolean'],
            'kk_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'members' => ['required', 'array', 'min:1'],
            'members.*.id' => ['nullable', 'integer'],
            'members.*.nik' => ['nullable', 'string', 'max:20'],
            'members.*.name' => ['required', 'string', 'max:100'],
            'members.*.gender' => ['nullable', 'in:L,P'],
            'members.*.birth_place' => ['nullable', 'string', 'max:100'],
            'members.*.birth_date' => ['nullable', 'date'],
            'members.*.religion' => ['nullable', 'string', 'max:30'],
            'members.*.education' => ['nullable', 'string', 'max:50'],
            'members.*.job' => ['nullable', 'string', 'max:100'],
            'members.*.marital_status' => ['nullable', 'string', 'max:30'],
            'members.*.family_relation' => ['nullable', 'string', 'max:50'],
            'members.*.occupancy_status' => ['nullable', 'in:pemilik,kontrak'],
            'members.*.phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'members.required' => 'Minimal harus ada 1 anggota keluarga yang didata.',
            'members.*.name.required' => 'Nama anggota keluarga wajib diisi.',
            'kk_image.max' => 'Ukuran file Kartu Keluarga maksimal 5 MB.',
        ];
    }
}
