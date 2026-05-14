<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoiceItemController;
use App\Http\Controllers\Admin\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::inertia('/', 'admin/Dashboard')->name('dashboard');

    Route::resource('invoices', InvoiceController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::patch('invoices/{invoice}/settings', [InvoiceController::class, 'updateSettings'])->name('invoices.settings.update');
    Route::get('invoices/{invoice}/view-logs', [InvoiceController::class, 'viewLogs'])->name('invoices.view-logs');
    Route::resource('invoices.invoice-items', InvoiceItemController::class)
        ->only(['store', 'update', 'destroy'])
        ->parameters(['invoice-items' => 'invoiceItem']);
    Route::resource('invoices.payments', PaymentController::class)->only(['store', 'update', 'destroy']);
    Route::resource('clients', ClientController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('clients/{client}/invoices', [ClientController::class, 'invoices'])->name('clients.invoices');
});
