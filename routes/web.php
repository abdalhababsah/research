<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');
});

// Admin-only routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin routes will be added here
});

// Researcher-only routes
Route::middleware(['auth', 'verified', 'researcher'])->prefix('researcher')->name('researcher.')->group(function () {
    // Researcher routes will be added here
});

require __DIR__.'/settings.php';
