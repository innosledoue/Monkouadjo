<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:expense,income'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'occurred_at' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,om,momo,wave,card,bank,other'],
            'beneficiary' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'uuid' => ['nullable', 'uuid'],
            'source' => ['nullable', 'in:manual,voice,sms,import'],
        ];
    }
}
