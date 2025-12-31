<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class SyncOrdersToCloud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-orders-to-cloud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync local orders to cloud API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Récupérer les commandes non synchronisées
        $orders = Order::where('sync_status', false)
                       ->with('items')
                       ->limit(50) // Batch pour éviter timeout
                       ->get();
    
        if ($orders->isEmpty()) return;
    
        // 2. Envoyer au Cloud
        $response = Http::withToken(env('CLOUD_API_KEY'))
                        ->post(env('CLOUD_URL') . '/api/sync/orders', $orders->toArray());
    
        // 3. Marquer comme synchronisé si succès
        if ($response->successful()) {
            Order::whereIn('uuid', $orders->pluck('uuid'))->update(['sync_status' => true]);
            $this->info('Synced ' . $orders->count() . ' orders.');
        } else {
            $this->error('Failed to sync orders: ' . $response->body());
        }
    }
}
