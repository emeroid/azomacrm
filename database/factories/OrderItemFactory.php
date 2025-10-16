<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Order;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }

    public function withOrder(Order $order)
    {
        return $this->state([
            'order_id' => $order->id,
        ]);
    }

    public function withProduct(Product $product)
    {
        return $this->state([
            'product_id' => $product->id,
            'unit_price' => $product->base_price,
        ]);
    }

    public function withQuantity(int $quantity)
    {
        return $this->state([
            'quantity' => $quantity,
        ]);
    }
}
