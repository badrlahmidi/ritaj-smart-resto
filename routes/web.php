<?php

use App\Livewire\CashRegisterLogic;
// use App\Livewire\Pos\PosLoginPage;
use App\Livewire\Pos\PosPage;
use App\Livewire\Pos\PosOrderPage;
use App\Livewire\Pos\PosPaymentPage;
use App\Livewire\Kds\KdsBoard;
use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Settings\GeneralSettings;

use App\Livewire\Pos\Terminal;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pos', Terminal::class)->name('pos.terminal')->middleware('auth');

Route::get('/admin/orders/{order}/print', function (Order $order) {
    return view('receipts.thermal', [
        'order' => $order->load('items.product', 'table', 'server'),
        'settings' => app(GeneralSettings::class),
    ]);
})->name('order.print')->middleware('auth');

// POS Routes
// Route::get('/pos/login', PosLoginPage::class)->name('pos.login');
Route::get('/pos', PosPage::class)->middleware('auth')->name('pos');
Route::get('/pos/order/{table}', PosOrderPage::class)->middleware('auth')->name('pos.order');
Route::get('/pos/payment/{order}', PosPaymentPage::class)->middleware('auth')->name('pos.payment');

// KDS Routes
Route::get('/kds', KdsBoard::class)->middleware('auth')->name('kds'); // Master KDS
Route::get('/kds/{station}', KdsBoard::class)->middleware('auth')->name('kds.station'); // Station specific
