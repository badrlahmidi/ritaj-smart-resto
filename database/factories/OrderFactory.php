<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'table_id' => Table::factory(),
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->address(),
            'status' => OrderStatus::Paid,
            'type' => OrderType::DINE_IN,
            'total_amount' => $this->faker->randomFloat(2, 50, 500),
            'discount_amount' => 0,
            'discount_type' => 'fixed',
            'service_charge' => 0,
            'tax_amount' => $this->faker->randomFloat(2, 0, 50),
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'notes' => null,
            'cancel_reason' => null,
            'sync_status' => false,
            'synced_at' => null,
            'is_stock_deducted' => false,
            'locked_by' => null,
            'locked_at' => null,
        ];
    }
}
