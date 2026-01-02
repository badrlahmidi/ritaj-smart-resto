<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncOrdersToCloud extends Command
{
    protected $signature = 'sync:orders 
                          {--limit=50 : Number of orders to sync per batch}
                          {--force : Force sync even for already synced orders}';
    
    protected $description = 'Synchronize local orders to cloud instance with improved retry logic';

    public function handle()
    {
        $limit = $this->option('limit');
        $force = $this->option('force');
        
        $query = Order::with(['items.product', 'table', 'server'])
            ->where('sync_status', false)
            ->where('status', 'paid');
        
        if (!$force) {
            $query->where('sync_status', false);
        }
        
        $orders = $query->limit($limit)->get();

        if ($orders->isEmpty()) {
            $this->info('✓ No orders to sync');
            return self::SUCCESS;
        }

        $this->info("📤 Syncing {$orders->count()} orders to cloud...");
        $this->newLine();
        
        $synced = 0;
        $failed = 0;
        $bar = $this->output->createProgressBar($orders->count());

        foreach ($orders as $order) {
            try {
                $response = Http::retry(3, 100, function ($exception, $request) {
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->timeout(10)
                ->withToken(config('services.cloud.api_token'))
                ->post(config('services.cloud.url').'/api/sync/orders', [
                    'uuid' => $order->uuid,
                    'local_id' => $order->local_id,
                    'order' => $order->only([
                        'uuid', 'local_id', 'status', 'total_amount', 
                        'payment_method', 'created_at', 'updated_at'
                    ]),
                    'items' => $order->items->map(fn($item) => [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price,
                        'subtotal' => $item->total_price,
                        'notes' => $item->notes,
                    ]),
                    'table' => $order->table?->only(['id', 'name', 'qr_code_hash']),
                    'server' => $order->server?->only(['id', 'name', 'email']),
                ]);

                if ($response->successful()) {
                    $order->update([
                        'sync_status' => true,
                        'synced_at' => now()
                    ]);
                    
                    $synced++;
                } else {
                    $failed++;
                    
                    Log::warning('Sync failed', [
                        'order_id' => $order->uuid,
                        'local_id' => $order->local_id,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                }
                
            } catch (\Exception $e) {
                $failed++;
                
                Log::error('Sync exception', [
                    'order_id' => $order->uuid,
                    'local_id' => $order->local_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        // Résumé
        $this->info("✓ Sync completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total', $orders->count()],
                ['✓ Synced', $synced],
                ['✗ Failed', $failed],
                ['Success Rate', round(($synced / $orders->count()) * 100, 2).'%']
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
