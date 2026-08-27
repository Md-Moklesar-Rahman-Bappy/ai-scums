<?php

declare(strict_types=1);

namespace App\Http\Requests\Fee;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * FeePaymentRequest.
 */
class FeePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
        ];
    }
}
