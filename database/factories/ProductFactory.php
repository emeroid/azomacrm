<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'base_price' => $this->faker->randomFloat(2, 10, 1000),
            'image_path' => $this->faker->imageUrl(),
            'is_active' => true,
            'created_by' => User::factory()->admin(),
        ];
    }

    public function inactive()
    {
        return $this->state([
            'is_active' => false,
        ]);
    }

    public function withCreator(User $user)
    {
        return $this->state([
            'created_by' => $user->id,
        ]);
    }
}
