<?php

use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PurchaseController::class, 'index'])->name('purchase.index');
Route::post('/pesanan', [PurchaseController::class, 'store'])->name('purchase.store');
