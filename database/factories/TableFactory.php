<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'name' => 'T'.$this->faker->unique()->numberBetween(1, 99),
            'capacity' => $this->faker->numberBetween(2, 8),
            'status' => 'available',
            'area_id' => Area::factory(),
            'position_x' => $this->faker->numberBetween(0, 500),
            'position_y' => $this->faker->numberBetween(0, 500),
            'shape' => $this->faker->randomElement(['square', 'round', 'rectangle']),
            'current_order_uuid' => null,
        ];
    }
}
