<?php

use App\Livewire\CashRegisterLogic;
use App\Livewire\Pos\PosLoginPage;
use App\Livewire\Pos\PosPage;
use App\Livewire\Pos\PosOrderPage;
use App\Livewire\Kds\KdsBoard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});

// POS Routes
Route::get('/pos/login', PosLoginPage::class)->name('pos.login');
Route::get('/pos', PosPage::class)->middleware('auth')->name('pos');
Route::get('/pos/order/{table}', PosOrderPage::class)->middleware('auth')->name('pos.order');

// KDS Routes
Route::get('/kds', KdsBoard::class)->middleware('auth')->name('kds'); // Master KDS
Route::get('/kds/{station}', KdsBoard::class)->middleware('auth')->name('kds.station'); // Station specific
