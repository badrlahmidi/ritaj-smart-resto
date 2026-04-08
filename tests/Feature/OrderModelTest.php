<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function order_auto_assigns_uuid_on_create(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $order->uuid
        );
    }

    #[Test]
    public function order_auto_increments_local_id(): void
    {
        $first = Order::factory()->create();
        $second = Order::factory()->create();

        $this->assertGreaterThan($first->local_id, $second->local_id);
    }

    #[Test]
    public function order_does_not_overwrite_provided_uuid(): void
    {
        $uuid = '11111111-2222-4333-8444-555555555555';
        $order = Order::factory()->create(['uuid' => $uuid]);

        $this->assertSame($uuid, $order->uuid);
    }

    #[Test]
    public function order_respects_provided_local_id(): void
    {
        $order = Order::factory()->create(['local_id' => 999]);

        $this->assertSame(999, $order->local_id);
    }

    #[Test]
    public function order_items_relation_uses_uuid_key(): void
    {
        $order = Order::factory()->create();

        // Relation exists and does not throw
        $this->assertCount(0, $order->items);
    }
}
