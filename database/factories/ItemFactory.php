<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     * This is set to null because Item is an abstract class 
     * and we will specify the model in the child factories (ProductFactory, ServiceFactory).
     *
     * @var string|null
     */
    protected $model = null; 

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'price' => fake()->randomFloat(2, 1, 100),
            'is_available' => fake()->boolean(),
            'item_type' => fake()->randomElement([\App\Enums\ItemType::PRODUCT, \App\Enums\ItemType::SERVICE]),
        ];
    }

    /**
     * Helper to state this should be a product
     */
    public function product(): static
    {
        return $this->state(fn (array $attributes) => ['item_type' => \App\Enums\ItemType::PRODUCT]);
    }

    /**
     * Helper to state this should be a service
     */
    public function service(): static
    {
        return $this->state(fn (array $attributes) => ['item_type' => \App\Enums\ItemType::SERVICE]);
    }
}
