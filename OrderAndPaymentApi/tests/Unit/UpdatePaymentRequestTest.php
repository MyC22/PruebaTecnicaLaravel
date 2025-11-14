<?php

namespace Tests\Unit;

use App\Http\Requests\UpdatePaymentRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePaymentRequestTest extends TestCase
{
    // test para validar actualización con campos correctos
    /** @test */
    public function it_allows_partial_update_and_validates_fields()
    {
        $request = new UpdatePaymentRequest();
        $data = [
            'status' => 'success',
            'amount' => 150,
            'payment_method' => 'paypal'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }

    // test para validar que falla con campos incorrectos
    /** @test */
    public function it_fails_with_invalid_fields()
    {
        $request = new UpdatePaymentRequest();
        $data = [
            'status' => 'invalid',
            'amount' => -10
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->messages());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }
}
