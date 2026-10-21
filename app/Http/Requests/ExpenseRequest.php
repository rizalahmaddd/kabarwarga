<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(array_keys(Expense::CATEGORIES))],
            'amount' => ['required', 'integer', 'min:1', 'max:100000000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'remove_receipt' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'receipt.mimes' => 'Nota harus berupa foto (JPG/PNG/WEBP) atau PDF.',
            'receipt.max' => 'Ukuran nota maksimal 5 MB.',
        ];
    }

    /**
     * Validated fields plus the stored receipt path. Replacing or removing a receipt deletes the old file.
     *
     * @return array<string, mixed>
     */
    public function payload(?Expense $expense = null): array
    {
        $data = collect($this->validated())->except(['receipt', 'remove_receipt'])->all();
        $data['category'] ??= 'lainnya';

        if ($this->hasFile('receipt') || $this->boolean('remove_receipt')) {
            $expense?->deleteReceipt();
            $data['receipt_path'] = $this->hasFile('receipt') ? $this->file('receipt')->store('nota', 'public') : null;
        }

        return $data;
    }
}
