<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
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
    Route::get('/dashboard',   [PetController::class, 'index'])->name('dashboard');
    Route::get('/pets/create', [PetController::class, 'create'])->name('pets.create');
    Route::post('/pets',       [PetController::class, 'store'])->name('pets.store');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    // Alerts
    Route::post('/alerts',                        [AlertController::class, 'store'])->name('alerts.store');
    Route::post('/alerts/test',                   [AlertController::class, 'testNotification'])->name('alerts.test');
    Route::post('/alerts/{alert}/resolve',        [AlertController::class, 'resolve'])->middleware('throttle:5,1')->name('alerts.resolve');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Pet Health - Saúde & Vet (Tela principal)
    Route::get('/pets/{pet}/health', [PetController::class, 'health'])->name('pets.health');
    Route::patch('/pets/{pet}/vet', [PetController::class, 'updateVet'])->name('pets.vet.update');

    // Health Records - Fichas Clínicas
    Route::post('/pets/{pet}/records', [HealthRecordController::class, 'store'])->name('pets.records.store');
    Route::post('/pets/{pet}/records/{record}/privacy', [HealthRecordController::class, 'updatePrivacy'])->name('pets.records.privacy');
    Route::get('/pets/{pet}/records/{record}/view', [HealthRecordController::class, 'showFile'])->name('pets.records.view');
    Route::delete('/pets/{pet}/records/{record}', [HealthRecordController::class, 'destroy'])->name('pets.records.destroy');

    // Schedules - Lembretes de Vacinas/Remédios
    Route::post('/pets/{pet}/schedules', [ScheduleController::class, 'store'])->name('pets.schedules.store');
    Route::post('/pets/{pet}/schedules/{schedule}/toggle', [ScheduleController::class, 'toggle'])->name('pets.schedules.toggle');
    Route::delete('/pets/{pet}/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('pets.schedules.destroy');
});
