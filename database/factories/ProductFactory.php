<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 10, 120);

        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'short_description' => $this->faker->sentence(4),
            'price' => $price,
            'cost' => round($price * 0.4, 2),
            'category_id' => Category::factory(),
            'is_available' => true,
            'has_stock' => false,
            'stock_quantity' => $this->faker->numberBetween(0, 200),
            'alert_threshold' => $this->faker->numberBetween(1, 10),
            'image_url' => null,
            'kitchen_station' => $this->faker->randomElement(['kitchen', 'bar', 'pizza']),
            'is_combo' => false,
        ];
    }
}
