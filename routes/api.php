<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyClientController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('clients')->group(function () {
    Route::post('/', [MyClientController::class, 'store']);
    Route::put('{slug}', [MyClientController::class, 'update']);
    Route::delete('{slug}', [MyClientController::class, 'destroy']);
    Route::get('{slug}', [MyClientController::class, 'show']);
});
