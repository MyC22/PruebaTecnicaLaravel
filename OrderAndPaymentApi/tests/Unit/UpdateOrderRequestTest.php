<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateOrderRequestTest extends TestCase
{
    use RefreshDatabase;

    // test para validar actualización especifica
    /** @test */
    public function it_allows_partial_update_and_validates_status()
    {
        $request = new UpdateOrderRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($key)
                {
                    return 1;
                }
            };
        });

        $data = [
            'status' => 'paid',
            'total_amount' => 200
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }

    // test para validar que falle con un estado o monto invalido
    /** @test */
    public function it_fails_with_invalid_status_or_amount()
    {
        $request = new UpdateOrderRequest();
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($key)
                {
                    return 1;
                }
            };
        });

        $data = [
            'status' => 'invalid',
            'total_amount' => -100
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->messages());
        $this->assertArrayHasKey('total_amount', $validator->errors()->messages());
    }
}
