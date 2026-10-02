<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HouseholdRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:30', Rule::unique('households')->ignore($this->route('household'))],
            'head_name' => ['required', 'string', 'max:100'],
            'occupancy_status' => ['nullable', 'in:pemilik,kontrak'],
            'kk_number' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['number.unique' => 'Nomor rumah ini sudah terdaftar.'];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return ['is_active' => $this->boolean('is_active')] + $this->validated();
    }
}
