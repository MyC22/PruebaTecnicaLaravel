<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{

    protected $model = Payment::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => $this->faker->numberBetween(1000, 50000),
            'status' => $this->faker->randomElement(['pending', 'success', 'failed']),
            'payment_method' => $this->faker->randomElement(['card', 'paypal', 'transfer']),
            'attempt_number' => $this->faker->numberBetween(1, 3),
            'external_reference' => $this->faker->uuid(),
        ];
    }
}
