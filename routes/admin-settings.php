<?php

use App\Http\Controllers\Admin\Settings\ProfileController;
use App\Http\Controllers\Admin\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
    Route::redirect('', '/admin/settings/profile');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('security', [SecurityController::class, 'edit'])->name('security.edit');
    Route::put('password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('security.update');

    Route::inertia('appearance', 'admin/Settings/Appearance')->name('appearance.edit');
});
