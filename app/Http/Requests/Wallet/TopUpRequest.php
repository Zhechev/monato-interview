<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['sometimes', 'string', 'in:credit_card,bank_transfer'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'The amount must be at least $0.01.',
            'payment_method.in' => 'The selected payment method is invalid.',
        ];
    }
}
