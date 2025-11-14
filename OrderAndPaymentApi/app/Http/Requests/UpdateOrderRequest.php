<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
        $orderId = $this->route('id');

        return [
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => [
                'email',
                'max:255',
                Rule::unique('orders', 'customer_email')->ignore($orderId),
            ],
            'customer_phone' => [
                'string',
                'max:50',
                Rule::unique('orders', 'customer_phone')->ignore($orderId),
            ],
            'total_amount' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'status' => 'sometimes|required|in:pending,paid,failed',
        ];
    }

    //Mensaje de errores
    public function messages()
    {
        return [
            'customer_email.unique' => 'El correo ya se encuentra registrado',
            'customer_phone.unique' => 'El numero ya se encuentra registrado',
            'status.in' => 'El estado debe ser uno de estos: pending, paid, failes',
            'total_amount.numeric' => 'El monto total debe ser un número válido',
        ];
    }
}
