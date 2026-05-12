<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('invoices', InvoiceController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::resource('invoices.tasks', TaskController::class)->only(['store', 'update', 'destroy']);
    Route::resource('invoices.payments', PaymentController::class)->only(['store', 'update', 'destroy']);
    Route::resource('clients', ClientController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});
