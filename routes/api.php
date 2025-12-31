<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Route de synchronisation reçue du Cloud
    Route::post('/sync/orders', function (Request $request) {
        // Logique de réception (Cloud Side)
        // Valider et insérer/mettre à jour les données venant du Local
        return response()->json(['status' => 'synced']);
    });

});
