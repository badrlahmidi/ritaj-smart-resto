<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderSyncTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function orders_are_synced_to_cloud_successfully()
    {
        Http::fake([
            '*/api/sync/orders' => Http::response(['success' => true], 200)
        ]);

        $order = Order::factory()->create([
            'sync_status' => false,
            'synced_at' => null,
        ]);

        $this->artisan('sync:orders')
            ->assertExitCode(0);

        $order->refresh();
        
        $this->assertTrue($order->sync_status);
        $this->assertNotNull($order->synced_at);
    }

    #[Test]
    public function sync_handles_connection_failures_gracefully()
    {
        Http::fake([
            '*/api/sync/orders' => Http::response([], 500)
        ]);

        $order = Order::factory()->create(['sync_status' => false]);

        $this->artisan('sync:orders')
            ->assertExitCode(1);

        $order->refresh();
        
        $this->assertFalse($order->sync_status);
        $this->assertNull($order->synced_at);
    }

    #[Test]
    public function sync_respects_limit_option()
    {
        Http::fake([
            '*/api/sync/orders' => Http::response(['success' => true], 200)
        ]);

        Order::factory()->count(10)->create(['sync_status' => false]);

        $this->artisan('sync:orders --limit=5')
            ->assertExitCode(0);

        $this->assertEquals(5, Order::where('sync_status', true)->count());
        $this->assertEquals(5, Order::where('sync_status', false)->count());
    }
}
