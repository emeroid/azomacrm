<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\OrderCommunication;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderCommunication>
 */
class OrderCommunicationFactory extends Factory
{
    protected $model = OrderCommunication::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'agent_id' => User::factory(),
            'type' => $this->faker->randomElement(['call', 'email', 'note']),
            'content' => $this->faker->paragraph,
            'labels' => json_encode($this->faker->words(3)),
            'outcome' => json_encode($this->faker->randomElement(OrderCommunication::OUTCOMES)),
        ];
    }

    public function call()
    {
        return $this->state([
            'type' => 'call',
        ]);
    }

    public function email()
    {
        return $this->state([
            'type' => 'email',
        ]);
    }

    public function note()
    {
        return $this->state([
            'type' => 'note',
        ]);
    }

    public function withOrder(Order $order)
    {
        return $this->state([
            'order_id' => $order->id,
        ]);
    }

    public function withAgent(User $agent)
    {
        return $this->state([
            'agent_id' => $agent->id,
        ]);
    }
}
