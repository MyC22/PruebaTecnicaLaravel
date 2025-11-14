<?php

namespace Tests\Unit;

use App\Http\Requests\StoreOrderRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;


class StoreOrderRequestTest extends TestCase
{
    use RefreshDatabase;

    // test para verificar que la validación falla si se dan datos vacios
    /** @test */
     public function it_fails_with_empty_data()
    {
        $request = new StoreOrderRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('customer_name', $validator->errors()->messages());
        $this->assertArrayHasKey('total_amount', $validator->errors()->messages());
        $this->assertArrayHasKey('currency', $validator->errors()->messages());
    }

    // test para verificar que la validación pasa con datos correctos
    /** @test */
    public function it_passes_with_valid_data()
    {
        $request = new StoreOrderRequest();

        $data = [
            'customer_name' => 'Denis Michael',
            'customer_email' => 'denis@example.com',
            'customer_phone' => '+51960455281',
            'total_amount' => 100,
            'currency' => 'PEN',
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }
}
