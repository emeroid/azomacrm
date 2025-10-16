<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    protected $model = User::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'mobile' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'),
            'role' => $this->faker->randomElement(['admin', 'marketer', 'call_agent', 'delivery_agent']),
            'is_blacklisted' => false,
        ];
    }

    public function admin()
    {
        return $this->state([
            'role' => 'admin',
        ]);
    }

    public function marketer()
    {
        return $this->state([
            'role' => 'marketer',
        ]);
    }

    public function callAgent()
    {
        return $this->state([
            'role' => 'call_agent',
        ]);
    }

    public function deliveryAgent()
    {
        return $this->state([
            'role' => 'delivery_agent',
        ]);
    }

    public function blacklisted()
    {
        return $this->state([
            'is_blacklisted' => true,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
