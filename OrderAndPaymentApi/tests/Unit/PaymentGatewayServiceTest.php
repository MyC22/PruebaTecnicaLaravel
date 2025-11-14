<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;


class PaymentGatewayServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentGatewayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentGatewayService();
    }

    // test que confirmar cuando el gateway retonrne correcto
    /** @test */
    public function it_returns_success_when_gateway_returns_success()
    {
        $order = Order::factory()->create();
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 1000,
            'attempt_number' => 1
        ]);
        Http::fake([
            $this->service->getUrl() => Http::response(['status' => 'success', 'reference' => 'ABC123'], 200)
        ]);
        $result = $this->service->confirmPayment($payment);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('ABC123', $result['reference']);
    }

    // test que confirma fallo cuando el gateway retonrne failed
    /** @test */
    public function it_returns_failed_when_gateway_returns_failed()
    {
        $order = Order::factory()->create();
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 1000,
            'attempt_number' => 1
        ]);
        Http::fake([
            $this->service->getUrl() => Http::response(['status' => 'failed', 'reference' => 'XYZ789'], 200)
        ]);
        $result = $this->service->confirmPayment($payment);
        $this->assertEquals('failed', $result['status']);
        $this->assertEquals('XYZ789', $result['reference']);
    }

    // test que confirma fallo cuando hay alguna excepcion en el gateway
    /** @test */
    public function it_returns_failed_when_exception_occurs()
    {
        $order = Order::factory()->create();
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 1000,
            'attempt_number' => 1
        ]);

        Http::fake([
            $this->service->getUrl() => Http::response(null, 500)
        ]);
        $result = $this->service->confirmPayment($payment);
        $this->assertEquals('failed', $result['status']);
        $this->assertNull($result['reference']);
    }
}
