<?php

use App\Livewire\CashRegisterLogic;
use App\Livewire\Pos\PosLoginPage;
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

// Protected POS Routes (To be grouped later)
// Route::get('/pos', PosPage::class)->middleware('auth');
