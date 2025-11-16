<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{

    use RefreshDatabase;

    //test para listar los pagos
    /** @test */
    public function can_list_payments()
    {
        Payment::factory()->count(3)->create();

        $response = $this->getJson('/api/payments');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    //test para buscar 1 pago
    /** @test */
    public function can_show_payment()
    {
        $payment = Payment::factory()->create();

        $response = $this->getJson("/api/payments/{$payment->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $payment->id);
    }

    // test para crear un pago
    /** @test */
    public function can_create_successful_payment()
    {
        $order = Order::factory()->create([
            'total_amount' => 10550,
            'status' => 'pending',
        ]);
        $mock = Mockery::mock(PaymentGatewayService::class);
        $mock->shouldReceive('confirmPayment')
            ->once()
            ->andReturn(['status' => 'success', 'reference' => 'REF123']);
        $this->app->instance(PaymentGatewayService::class, $mock);
        $amount = $order->total_amount / 100;

        $response = $this->postJson("/api/orders/{$order->id}/payments", [
            'amount' => $amount,
            'payment_method' => 'tarjeta',
        ]);
        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'success')
            ->assertJsonPath('data.external_reference', 'REF123')
            ->assertJsonPath('message', 'Pago creado correctamente');
        $responseAmount = floatval($response->json('data.amount'));
        $this->assertEqualsWithDelta($amount, $responseAmount, 0.01);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'success',
            'external_reference' => 'REF123',
            'amount' => $order->total_amount
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }

    // test para actualizar un pago
    /** @test */
    public function can_update_payment()
    {
        $payment = Payment::factory()->create();

        $payload = [
            'payment_method' => 'pagoefectivo',
            'amount' => 504.5,
        ];

        $response = $this->putJson("/api/payments/{$payment->id}/update", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.payment_method', 'pagoefectivo')
            ->assertJsonPath('data.amount', 504.5);
    }





    // test para remover un pago logicamente
    /** @test */
    public function can_soft_delete_payment()
    {
        $payment = Payment::factory()->create();

        $response = $this->deleteJson("/api/payments/{$payment->id}/delete");
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pago eliminado correctamente');

        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }

    // test para restaurar un pago removido
    /** @test */
    public function can_restore_payment()
    {
        $payment = Payment::factory()->create();
        $payment->delete();

        $response = $this->postJson("/api/payments/{$payment->id}/restore");
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pago restaurado correctamente');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'deleted_at' => null]);
    }

    //test para listar pagos exitosos
    /** @test */
    public function can_list_success_payments()
    {
        Payment::factory()->count(2)->create(['status' => 'success']);

        $response = $this->getJson('/api/payments/success');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // test para listar pagos fallidos
    /** @test */
    public function can_list_failed_payments()
    {
        Payment::factory()->count(2)->create(['status' => 'failed']);

        $response = $this->getJson('/api/payments/failed');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // test para listar pagos eliminados
    /** @test */
    public function can_list_trashed_payments()
    {
        $payment = Payment::factory()->create();
        $payment->delete();

        $response = $this->getJson('/api/payments/trashed');
        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $payment->id);
    }
}
