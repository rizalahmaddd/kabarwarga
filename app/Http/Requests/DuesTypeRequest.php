<?php

namespace App\Http\Requests;

use App\Models\DuesType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DuesTypeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'integer', 'min:0', 'max:100000000'],
            'frequency' => ['required', Rule::in([DuesType::MONTHLY, DuesType::ONCE])],
            'starts_on' => ['nullable', 'date_format:Y-m'],
            'due_on' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validated();

        $data['starts_on'] = $data['frequency'] === DuesType::MONTHLY && ! empty($data['starts_on']) ? $data['starts_on'].'-01' : null;
        $data['due_on'] = $data['frequency'] === DuesType::ONCE ? ($data['due_on'] ?? null) : null;
        $data['is_active'] = $this->boolean('is_active');

        return $data;
    }
}
