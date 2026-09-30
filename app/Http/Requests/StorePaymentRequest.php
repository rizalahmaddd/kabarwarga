<?php

namespace App\Http\Requests;

use App\Models\DuesType;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'household_id' => ['required', Rule::exists('households', 'id')],
            'dues_type_id' => ['required', Rule::exists('dues_types', 'id')],
            'periods' => [Rule::requiredIf(fn () => (bool) $this->duesType()?->isMonthly()), 'array'],
            'periods.*' => ['regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'amount' => ['required', 'integer', 'min:0', 'max:100000000'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['periods.required' => 'Centang minimal satu bulan yang dibayar.'];
    }

    public function duesType(): ?DuesType
    {
        return DuesType::find($this->integer('dues_type_id'));
    }
}
