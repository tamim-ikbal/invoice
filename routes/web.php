<?php

use App\Http\Controllers\Public\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('invoice/{uid}', [InvoiceController::class, 'show'])->name('public.invoice.show');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/admin-settings.php';
