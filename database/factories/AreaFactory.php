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
            'name' => sprintf('Zone %s %s', strtoupper($this->faker->bothify('??')), $this->faker->unique()->numberBetween(1, 9999)),
            'is_active' => true,
        ];
    }
}
