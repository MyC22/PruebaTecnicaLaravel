<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:tarjeta,paypal,yape,pagoefectivo']
        ];
    }


    //Mensaje de errores
    public function messages()
    {
        return[
            'amount.required' => 'El monto es obligatorio',
            'amount.integer' => 'El monto debe ser un número válido',
            'amount.min' => 'El monto debe ser mayor a 0',

            'payment_method.required' => 'El metodo de pago es obligatorio',
            'payment_method.in' => 'El metodo de pago debe ser de: Tarjeta, Paypal, Yape, Pagoefectivo',
        ];


    }
}
