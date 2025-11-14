<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerErrorsTest extends TestCase
{
    use RefreshDatabase;

    // test para mostrar un pago que no existe
    /** @test */
    public function cannot_show_nonexistent_payment()
    {
        $this->getJson('/api/payments/9999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Pago encontrado');
    }

    // test para mostrar un pago ya eliminado
    /** @test */
    public function cannot_show_deleted_payment()
    {
        $payment = Payment::factory()->create();
        $payment->delete();
        $this->getJson("/api/payments/{$payment->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Este pago esta eliminado');
    }

    // test para actualizar un pago que no existe
    /** @test */
    public function cannot_update_nonexistent_payment()
    {
        $payload = ['amount' => 1000];
        $this->putJson('/api/payments/9999/update', $payload)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Pago no encontrado');
    }

    // test para actualizar un pago que no exsite porque fue eliminado
    /** @test */
    public function cannot_update_deleted_payment()
    {
        $payment = Payment::factory()->create();
        $payment->delete();
        $payload = ['amount' => 1000];
        $this->putJson("/api/payments/{$payment->id}/update", $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Este pago esta eliminado y no se puede actualizar');
    }

    // test para eliminar un pago que no exsite
    /** @test */
    public function cannot_delete_nonexistent_payment()
    {
        $this->deleteJson('/api/payments/9999/delete')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Pago no encontrado');
    }

    // test para tratar de eliminar un pago que ya fue eliminado
    /** @test */
    public function deleting_already_deleted_payment_returns_error()
    {
        $payment = Payment::factory()->create();
        $this->deleteJson("/api/payments/{$payment->id}/delete")->assertStatus(200);
        $this->deleteJson("/api/payments/{$payment->id}/delete")
            ->assertStatus(404)
            ->assertJsonPath('message', 'Pago no encontrado');
    }

    // test para pagar pagar una orden eliminada
    /** @test */
    public function cannot_pay_deleted_order()
    {
        $order = Order::factory()->create();
        $order->delete();
        $payload = ['amount' => 1000, 'payment_method' => 'tarjeta'];
        $this->postJson(route('payments.register', ['order' => $order->id]), $payload)
            ->assertStatus(404);
    }

    // test para pagar una orden ya se pago
    /** @test */
    public function cannot_pay_already_paid_order()
    {
        $order = Order::factory()->create(['status' => 'paid']);
        $payload = ['amount' => $order->total_amount, 'payment_method' => 'tarjeta'];
        $this->postJson("/api/orders/{$order->id}/payments", $payload)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Pedido ya pagado');
    }

    // test para pagar con un monto que no es el correcto
    /** @test */
    public function cannot_pay_with_incorrect_amount()
    {
        $order = Order::factory()->create(['status' => 'pending', 'total_amount' => 1000]);
        $payload = ['amount' => 500, 'payment_method' => 'tarjeta'];
        $this->postJson("/api/orders/{$order->id}/payments", $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'El monto del pago debe ser igual al total del pedido');
    }
}
