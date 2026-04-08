<?php

namespace Database\Factories;

use App\Models\Printer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Printer>
 */
class PrinterFactory extends Factory
{
    protected $model = Printer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company().' Printer',
            'type' => $this->faker->randomElement(['network', 'windows', 'dummy']),
            'ip_address' => $this->faker->ipv4(),
            'path' => $this->faker->randomElement(['192.168.1.100', 'KitchenPrinter', 'php://stdout']),
            'port' => 9100,
            'station_tags' => [$this->faker->randomElement(['cashier', 'kitchen', 'bar', 'pizza'])],
            'paper_width' => 80,
            'auto_cut' => true,
            'cash_drawer' => false,
            'max_retries' => 3,
            'is_active' => true,
        ];
    }
}
