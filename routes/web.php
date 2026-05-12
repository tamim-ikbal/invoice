<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('invoice/{uid}', [\App\Http\Controllers\Public\InvoiceController::class, 'show'])->name('public.invoice.show');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
