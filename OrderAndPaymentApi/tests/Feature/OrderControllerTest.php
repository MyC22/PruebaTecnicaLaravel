<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;
    
    //test para listar todas las ordenes
    /** @test */
    public function can_list_orders()
    {
        Order::factory()->count(3)->create();
        $response = $this->getJson('/api/orders');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    //test para crear orden
    /** @test */
    public function can_create_order()
    {
        $payload = [
            'customer_name' => 'Denis Michael',
            'customer_email' => 'denis@example.com',
            'customer_phone' => '+51960455281',
            'total_amount' => 503.50,
            'currency' => 'PEN',
        ];

        $response = $this->postJson('/api/orders/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.customer_name', 'Denis Michael');

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'denis@example.com',
            'total_amount' => 50350, // en centavos
        ]);
    }

    //test para buscar order
    /** @test */
    public function can_show_order()
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");
        $response->assertStatus(200)
            ->assertJsonPath('id', $order->id);
    }

    //test para actualizar orden
    /** @test */
    public function can_update_order()
    {
        $order = Order::factory()->create([
            'customer_name' => 'Old Name'
        ]);

        $payload = ['customer_name' => 'New Name'];

        $response = $this->putJson("/api/orders/{$order->id}", $payload);
        $response->assertStatus(200)
            ->assertJsonPath('order.customer_name', 'New Name');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'New Name'
        ]);
    }

    //test para remover logicamente una orden
    /** @test */
    public function can_soft_delete_order()
    {
        $order = Order::factory()->create();

        $response = $this->deleteJson("/api/orders/{$order->id}");
        $response->assertStatus(200)
            ->assertJsonPath('order_id', $order->id);

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    //test para restaurar una orden
    /** @test */
    public function can_restore_order()
    {
        $order = Order::factory()->create();
        $order->delete();

        $response = $this->postJson("/api/orders/{$order->id}/restore");
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pedido restaurado correctamente');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'deleted_at' => null]);
    }

    //test para listar todas las ordenes eliminadas
    /** @test */
    public function can_list_trashed_orders()
    {
        $order = Order::factory()->create();
        $order->delete();

        $response = $this->getJson('/api/orders/trashed');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    //test para listar 1 orden en especifico eliminanda
    /** @test */
    public function can_show_trashed_order()
    {
        $order = Order::factory()->create();
        $order->delete();

        $response = $this->getJson("/api/orders/trashed/{$order->id}");
        $response->assertStatus(200)
            ->assertJsonPath('id', $order->id);
    }

    //test para listar las ordenes por su status
    /** @test */
    public function can_list_orders_by_status()
    {
        $pending = Order::factory()->create(['status' => 'pending']);
        $paid = Order::factory()->create(['status' => 'paid']);
        $failed = Order::factory()->create(['status' => 'failed']);

        $this->getJson('/api/orders/pending')->assertJsonFragment(['id' => $pending->id]);
        $this->getJson('/api/orders/paid')->assertJsonFragment(['id' => $paid->id]);
        $this->getJson('/api/orders/failed')->assertJsonFragment(['id' => $failed->id]);
    }
}
