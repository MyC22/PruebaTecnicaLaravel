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
    public function can_store_payment()
    {
        $order = Order::factory()->create(['total_amount' => 10000, 'status' => 'pending']);

        $this->instance(PaymentGatewayService::class, Mockery::mock(PaymentGatewayService::class, function ($mock) {
            $mock->shouldReceive('confirmPayment')
                ->andReturn(['status' => 'success', 'reference' => 'REF123']);
        }));

        $payload = [
            'amount' => 100,
            'payment_method' => 'tarjeta',
        ];

        $response = $this->postJson("/api/orders/{$order->id}/payments", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Pago creado correctamente');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'success',
        ]);
    }

    // test para actualizar un pago
    /** @test */
    public function can_update_payment()
    {
        $payment = Payment::factory()->create();

        $payload = [
            'payment_method' => 'pagoefectivo', // cambiar 'efectivo' por uno válido
            'amount' => 504.5, // opcional, si quieres actualizar también el monto
        ];

        $response = $this->putJson("/api/payments/{$payment->id}/update", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.payment_method', 'pagoefectivo')
            ->assertJsonPath('data.amount', 504.5); // si actualizas monto
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
