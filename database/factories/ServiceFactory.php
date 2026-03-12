<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //Create the base item manually to get the ID
            'item_id' => function () {
                return DB::table('items')->insertGetId([
                    'name' => $this->faker->words(3, true),
                    'price' => $this->faker->randomFloat(2, 10, 500),
                    'is_available' => true,
                    'item_type' => \App\Enums\ItemType::SERVICE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            },
            'required_product_id' => null, // By default, no dependency
        ];
    }

    /**
     * State to create a service that depends on a product.
     */
    public function withDependency(): static
    {
        return $this->state(fn (array $attributes) => [
            'required_product_id' => \App\Models\Product::factory()->create()->item_id,
        ]);
    }
}
