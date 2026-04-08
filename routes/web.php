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

Route::get('/pos/terminal', Terminal::class)->name('pos.terminal')->middleware('auth');

Route::get('/admin/orders/{order}/print', function (Order $order) {
    return view('receipts.thermal', [
        'order' => $order->load('items.product', 'table', 'server'),
        'settings' => app(GeneralSettings::class),
    ]);
})->name('order.print')->middleware('auth');

Route::get('/pos', PosPage::class)->middleware('auth')->name('pos');
Route::get('/pos/order/{table}', PosOrderPage::class)->middleware('auth')->name('pos.order');
Route::get('/pos/payment/{order}', PosPaymentPage::class)->middleware('auth')->name('pos.payment');

// KDS Routes
Route::get('/kds', KdsBoard::class)->middleware('auth')->name('kds'); // Master KDS
Route::get('/kds/{station}', KdsBoard::class)->middleware('auth')->name('kds.station'); // Station specific
