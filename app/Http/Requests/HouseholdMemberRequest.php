<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class HouseholdMemberRequest extends FormRequest
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
            'nik' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'education' => ['nullable', 'string', 'max:50'],
            'job' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'family_relation' => ['nullable', 'string', 'max:50'],
            'occupancy_status' => ['nullable', 'in:pemilik,kontrak'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
