<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerErrorsTest extends TestCase
{
     use RefreshDatabase;
    
    // test para crear una orden con datos incorrectos
    /** @test */
    public function cannot_create_order_with_invalid_data()
    {
        $payload = ['customer_name' => '', 'total_amount' => null];
        $this->postJson('/api/orders/register', $payload)
             ->assertStatus(422)
             ->assertJsonStructure(['message', 'errors']);
    }

    // test para mostrar una orden que no existe
    /** @test */
    public function show_nonexistent_order_returns_404()
    {
        $this->getJson('/api/orders/9999')->assertStatus(404)
             ->assertJsonPath('message', 'Orden no encontrada');
    }

    // test para actualizar una orden que ya fue eliminada
    /** @test */
    public function update_trashed_order_returns_422()
    {
        $order = Order::factory()->create();
        $order->delete();

        $payload = ['customer_name' => 'Nuevo'];
        $this->putJson("/api/orders/{$order->id}", $payload)
             ->assertStatus(422)
             ->assertJsonPath('message', 'No se puede actualizar un pedido eliminado');
    }

    // test para eliminar una orden que no existe
    /** @test */
    public function delete_nonexistent_order_returns_404()
    {
        $this->deleteJson('/api/orders/9999')->assertStatus(404)
             ->assertJsonPath('message', 'Pedido no encontrado');
    }

    // test para intentar eliminar otra vez una orden que ya fue eliminada
    /** @test */
    public function deleting_already_deleted_order_returns_422()
    {
        $order = Order::factory()->create();
        $this->deleteJson("/api/orders/{$order->id}")->assertStatus(200);
        // try delete again
        $this->deleteJson("/api/orders/{$order->id}")->assertStatus(422)
             ->assertJsonPath('message', 'Este pedido ya está eliminado');
    }
}
