<?php

namespace App\Http\Requests;

use App\Models\BankAccount;
use App\Support\Qris;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class BankAccountRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:60'],
            'account_number' => ['nullable', 'string', 'max:60'],
            'account_name' => ['nullable', 'string', 'max:100'],
            'qris' => ['nullable', 'image', 'max:3072'],
            'remove_qris' => ['boolean'],
            'qris_payload' => ['nullable', 'string', 'max:512'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $account = $this->account();
                $keepsQris = ($account?->qris_path && ! $this->boolean('remove_qris')) || $this->hasFile('qris') || $this->qrisPayload() !== null;

                if (blank($this->input('account_number')) && ! $keepsQris) {
                    $validator->errors()->add('account_number', 'Isi nomor rekening, atau unggah gambar QRIS.');
                }

                $payload = $this->qrisPayload();
                if ($payload !== null && ! Qris::isValid($payload)) {
                    $validator->errors()->add('qris_payload', 'Kode QRIS tidak valid. Pastikan gambar QRIS jelas atau salin ulang kodenya.');
                } elseif ($payload !== null && ! Qris::isStatic($payload)) {
                    $validator->errors()->add('qris_payload', 'Ini QRIS dinamis yang sudah berisi nominal. Unggah QRIS statis dari merchant.');
                }
            },
        ];
    }

    /**
     * Validated data with the QRIS upload stored and any replaced image removed.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validated();
        $account = $this->account();

        $data['is_active'] = $this->boolean('is_active');

        if ($this->hasFile('qris') || $this->boolean('remove_qris')) {
            if ($account?->qris_path) {
                Storage::disk('public')->delete($account->qris_path);
            }
            $data['qris_path'] = $this->hasFile('qris') ? $this->file('qris')->store('qris', 'public') : null;
        }

        if ($this->has('qris_payload') || $this->hasFile('qris') || $this->boolean('remove_qris')) {
            $data['qris_payload'] = $this->qrisPayload();
        }

        unset($data['qris'], $data['remove_qris']);
        if (! isset($data['position'])) {
            unset($data['position']);
        }

        return $data;
    }

    private function qrisPayload(): ?string
    {
        if ($this->boolean('remove_qris') && ! $this->hasFile('qris')) {
            return null;
        }

        $payload = trim((string) $this->input('qris_payload'));

        return $payload === '' ? null : $payload;
    }

    private function account(): ?BankAccount
    {
        $account = $this->route('bankAccount');

        return $account instanceof BankAccount ? $account : null;
    }
}
