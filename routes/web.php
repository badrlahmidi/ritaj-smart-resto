<?php

use App\Livewire\Kds\KdsBoard;
use App\Livewire\Pos\PosOrderPage;
use App\Livewire\Pos\PosPage;
use App\Livewire\Pos\PosPaymentPage;
use App\Livewire\Pos\Terminal;
use App\Models\Order;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// POS routes — accessible to all authenticated staff (admin, manager, server)
Route::middleware(['auth', 'role:admin,manager,server'])->group(function () {
    Route::get('/pos', PosPage::class)->name('pos');
    Route::get('/pos/order/{table}', PosOrderPage::class)->name('pos.order');
    Route::get('/pos/payment/{order}', PosPaymentPage::class)->name('pos.payment');
    Route::get('/pos/terminal', Terminal::class)->name('pos.terminal');
});

// KDS routes — accessible to kitchen staff (and managers/admins)
Route::middleware(['auth', 'role:admin,manager,kitchen'])->group(function () {
    Route::get('/kds', KdsBoard::class)->name('kds');
    Route::get('/kds/{station}', KdsBoard::class)->name('kds.station');
});

// Receipt printing — any authenticated user
Route::get('/admin/orders/{order}/print', function (Order $order) {
    return view('receipts.thermal', [
        'order' => $order->load('items.product', 'table', 'server'),
        'settings' => app(GeneralSettings::class),
    ]);
})->name('order.print')->middleware('auth');
