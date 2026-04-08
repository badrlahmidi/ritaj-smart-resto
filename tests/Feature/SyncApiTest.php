<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

    #[Test]
    public function unauthenticated_request_is_rejected(): void
    {
        $this->postJson('/api/sync/orders', [])->assertStatus(401);
    }

    #[Test]
    public function valid_order_is_synced_and_persisted(): void
    {
        $this->authenticatedUser();
        $uuid = \Illuminate\Support\Str::uuid()->toString();

        $payload = [
            'uuid' => $uuid,
            'order' => [
                'status' => 'paid',
                'total_amount' => 89.50,
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'type' => 'takeaway',
            ],
            'items' => [
                [
                    'product_name' => 'Test Burger',
                    'quantity' => 2,
                    'price' => 44.75,
                    'subtotal' => 89.50,
                ],
            ],
            'server' => ['name' => 'Serveur Test', 'email' => 'sync-test@ritaj.local'],
        ];

        $this->postJson('/api/sync/orders', $payload)
            ->assertOk()
            ->assertJsonFragment(['status' => 'synced', 'order_uuid' => $uuid]);

        $this->assertDatabaseHas('orders', ['uuid' => $uuid, 'sync_status' => true]);
        $this->assertDatabaseCount('order_items', 1);
    }

    #[Test]
    public function idempotent_sync_updates_existing_order(): void
    {
        $this->authenticatedUser();
        $existingOrder = Order::factory()->create(['sync_status' => false]);

        $payload = [
            'uuid' => $existingOrder->uuid,
            'order' => [
                'status' => 'paid',
                'total_amount' => 55.00,
                'payment_status' => 'paid',
                'payment_method' => 'card',
                'type' => 'dine_in',
            ],
            'items' => [],
        ];

        $this->postJson('/api/sync/orders', $payload)->assertOk();

        $existingOrder->refresh();
        $this->assertTrue($existingOrder->sync_status);
        $this->assertSame('55.00', number_format((float) $existingOrder->total_amount, 2));
    }

    #[Test]
    public function sync_with_known_table_links_by_qr_code(): void
    {
        $this->authenticatedUser();
        $table = Table::factory()->create(['qr_code_hash' => 'abc123']);

        $payload = [
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'order' => ['status' => 'paid', 'total_amount' => 30.00],
            'items' => [],
            'table' => ['name' => 'T1', 'qr_code_hash' => 'abc123'],
        ];

        $this->postJson('/api/sync/orders', $payload)->assertOk();

        $order = Order::where('table_id', $table->id)->first();
        $this->assertNotNull($order);
    }

    #[Test]
    public function validation_rejects_missing_required_fields(): void
    {
        $this->authenticatedUser();

        $this->postJson('/api/sync/orders', [])->assertUnprocessable();
        $this->postJson('/api/sync/orders', ['uuid' => 'not-a-uuid', 'order' => []])->assertUnprocessable();
    }
}
