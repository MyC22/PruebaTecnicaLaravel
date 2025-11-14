<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
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
            'status' => 'sometimes|required|in:pending,success,failed',
            'amount' => 'sometimes|required|integer|min:1',
            'payment_method' => 'sometimes|required|string|max:50',
        ];
    }

    //Mensaje de errores
    public function messages(): array
    {
        return [
            'status.in' => 'El estado debe ser: pending, success o failed',
            'amount.integer' => 'El monto debe ser un numero',
        ];
    }
}
