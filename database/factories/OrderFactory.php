<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Order;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'status' => Order::STATUS_PROCESSING,
            'notes' => $this->faker->sentence,
            'marketer_id' => User::factory(),
            'call_agent_id' => User::factory(),
            'delivery_agent_id' => null,
            'delivery_notes' => null,
            'email' => $this->faker->optional()->safeEmail,
            'mobile' => $this->faker->phoneNumber,
            'phone' => $this->faker->optional()->phoneNumber,
            'address' => $this->faker->address,
            'state' => $this->faker->state,
            'full_name' => $this->faker->name,
        ];
    }

    public function processing()
    {
        return $this->state([
            'status' => Order::STATUS_PROCESSING,
        ]);
    }

    public function inTransit()
    {
        return $this->state([
            'status' => Order::STATUS_IN_TRANSIT,
            'delivery_agent_id' => User::factory(),
        ]);
    }

    public function delivered()
    {
        return $this->state([
            'status' => Order::STATUS_DELIVERED,
            'delivery_agent_id' => User::factory(),
        ]);
    }

    public function cancelled()
    {
        return $this->state([
            'status' => Order::STATUS_CANCELLED,
        ]);
    }

    public function withMarketer($marketer)
    {
        return $this->state([
            'marketer_id' => $marketer instanceof \Illuminate\Database\Eloquent\Model ? $marketer->id : $marketer,
        ]);
    }
    
    public function withCallAgent($callAgent)
    {
        return $this->state([
            'call_agent_id' => $callAgent instanceof \Illuminate\Database\Eloquent\Model ? $callAgent->id : $callAgent,
        ]);
    }
    
    public function withDeliveryAgent($deliveryAgent)
    {
        return $this->state([
            'delivery_agent_id' => $deliveryAgent instanceof \Illuminate\Database\Eloquent\Model ? $deliveryAgent->id : $deliveryAgent,
        ]);
    }
}
