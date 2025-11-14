<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
            'customer_name' => 'required|string|max:255',
            'customer_email' => [
                'email',
                'max:255',
                Rule::unique('orders', 'customer_email'),
            ],
            'customer_phone' => [
                'string',
                'max:50',
                Rule::unique('orders', 'customer_phone'),
            ],
            'total_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
        ];
    }

    /* Mensaje de errores */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'El nombre del cliente es obligatorio',
            'customer_email.email' => 'El correo debe tener un formato valido',
            'customer_email.unique' => 'El correo ya se encuentra registrado',
            'customer_phone.required' => 'El numero de telefono ya se encuentra registrado',
            'total_amount.required' => 'El monto total de la orden es obligatorio',
            'total_amount.numeric' => 'El monto total debe ser un número valido',
            'currency.size' => 'La moneda debe ser un código ISO de 3 letras '
        ];
    }
}
