<?php

namespace Tests\Unit;

use App\Http\Requests\StorePaymentRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePaymentRequestTest extends TestCase
{
    // test para verificar que la validación falla si tiene datos vacíos
    /** @test */
    public function it_fails_with_empty_data()
    {
        $request = new StorePaymentRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
        $this->assertArrayHasKey('payment_method', $validator->errors()->messages());
    }

    // test para verificar que la validacion funciona con datos correctos
    /** @test */
    public function it_passes_with_valid_data()
    {
        $request = new StorePaymentRequest();
        $data = [
            'amount' => 100,
            'payment_method' => 'tarjeta'
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }

    // test para verificar que la validación falla con datos incorrectos
    /** @test */
    public function it_fails_with_invalid_payment_method_or_amount()
    {
        $request = new StorePaymentRequest();
        $data = [
            'amount' => 0,
            'payment_method' => 'bitcoin'
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
        $this->assertArrayHasKey('payment_method', $validator->errors()->messages());
    }
}
