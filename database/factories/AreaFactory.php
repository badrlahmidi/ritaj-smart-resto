<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Salle', 'Terrasse', 'VIP']).' '.$this->faker->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
