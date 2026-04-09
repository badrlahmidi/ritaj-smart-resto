<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderItemStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SyncOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function orders(SyncOrderRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $order = DB::transaction(function () use ($payload): Order {
            $server = $this->resolveServer($payload['server'] ?? null);
            $table = $this->resolveTable($payload['table'] ?? null);

            $orderData = $payload['order'];

            // Build create-only fields separately so local_id is never overwritten
            $existing = Order::where('uuid', $payload['uuid'])->first();

            $sharedData = [
                'user_id' => $server->id,
                'table_id' => $table?->id,
                'status' => $orderData['status'],
                'payment_status' => $orderData['payment_status'] ?? 'paid',
                'payment_method' => $orderData['payment_method'] ?? null,
                'type' => $orderData['type'] ?? 'takeaway',
                'total_amount' => $orderData['total_amount'],
                'sync_status' => true,
                'synced_at' => now(),
            ];

            if ($existing) {
                $existing->update($sharedData);
                $order = $existing;
            } else {
                $order = Order::create(array_merge($sharedData, [
                    'uuid' => $payload['uuid'],
                    'local_id' => $payload['local_id'] ?? $orderData['local_id'] ?? null,
                ]));
            }

            $order->items()->delete();

            foreach ($payload['items'] ?? [] as $item) {
                $product = $this->resolveProduct($item);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['subtotal'] ?? ($item['price'] * $item['quantity']),
                    'notes' => $item['notes'] ?? null,
                    'status' => OrderItemStatus::Served,
                    'printed_kitchen' => true,
                    'printed_at' => now(),
                ]);
            }

            return $order->load('items.product', 'table', 'server');
        });

        return response()->json([
            'status' => 'synced',
            'order_uuid' => $order->uuid,
            'items_count' => $order->items->count(),
        ]);
    }

    private function resolveServer(?array $server): User
    {
        // Only look up existing, active users — never auto-create accounts via the sync API
        // as that would allow any authenticated sync client to create arbitrary user records.
        if (! empty($server['email'])) {
            $existing = User::where('email', $server['email'])
                ->where('is_active', true)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        // Fall back to the authenticated API user that made this request.
        /** @var User $apiUser */
        $apiUser = auth()->user();

        return $apiUser;
    }

    private function resolveTable(?array $table): ?Table
    {
        if (empty($table['name']) && empty($table['qr_code_hash'])) {
            return null;
        }

        $lookup = ! empty($table['qr_code_hash'])
            ? ['qr_code_hash' => $table['qr_code_hash']]
            : ['name' => $table['name']];

        return Table::updateOrCreate($lookup, [
            'name' => $table['name'] ?? 'Table synchronisée',
        ]);
    }

    private function resolveProduct(array $item): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'Menu Synchronisé'],
            ['is_active' => false]
        );

        $name = $item['product_name'] ?? 'Produit synchronisé';

        return Product::updateOrCreate(
            ['name' => $name],
            [
                'category_id' => $category->id,
                'price' => $item['price'],
                'cost' => 0,
                'is_available' => false,
                'has_stock' => false,
                'kitchen_station' => 'sync',
            ]
        );
    }
}
