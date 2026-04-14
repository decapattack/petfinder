<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

// ─── Home ────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ─── Breeze auth routes (login, register, password, verification, logout) ────
require __DIR__.'/auth.php';

// ─── OAuth (Socialite) ───────────────────────────────────────────────────────
Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirectToProvider'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->name('auth.social.callback');

// ─── Public (no auth required) ───────────────────────────────────────────────
Route::get('/pet/{uuid}', [PetController::class, 'showPublic'])->name('pets.public');

// ─── Protected (logged in + email verified) ──────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Pets
    Route::get('/dashboard',   [PetController::class, 'index'])->name('pets.index');
    Route::get('/pets/create', [PetController::class, 'create'])->name('pets.create');
    Route::post('/pets',       [PetController::class, 'store'])->name('pets.store');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    // Alerts
    Route::post('/alerts',                         [AlertController::class, 'store'])->name('alerts.store');
    Route::post('/alerts/test',                    [AlertController::class, 'testNotification'])->name('alerts.test');
    Route::post('/alerts/{alert}/resolve',         [AlertController::class, 'resolve'])->name('alerts.resolve');
});
