<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

// ─── Health Check (monitoramento de disponibilidade) ─────────────────────────
// Protegido por token secreto via header X-Health-Token.
// Em ambiente local/testing o token não é exigido.
// Configure APP_HEALTH_TOKEN no .env para uso em produção.
Route::get('/up', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]);
})->middleware('health.token')->name('health');

// ─── Home ────────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// ─── Breeze auth routes (login, register, password, verification, logout) ────
require __DIR__.'/auth.php';

// ─── OAuth (Socialite) ───────────────────────────────────────────────────────
Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirectToProvider'])->middleware('throttle:10,1')->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->middleware('throttle:10,1')->name('auth.social.callback');

// ─── Public (no auth required) ───────────────────────────────────────────────
Route::get('/pet/{uuid}', [PetController::class, 'showPublic'])->name('pets.public');
Route::get('/pet/{uuid}/map', [PetController::class, 'showPublicMap'])->name('pets.public.map');

// ─── Protected (logged in + email verified) ──────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // ── Pets ─────────────────────────────────────────────────────────────────
    Route::get('/dashboard',   [PetController::class, 'index'])->name('dashboard');
    Route::get('/pets/create', [PetController::class, 'create'])->name('pets.create');
    // Limite: máximo 20 pets criados por hora (proteção contra bots)
    Route::post('/pets',       [PetController::class, 'store'])->middleware('throttle:20,60')->name('pets.store');
    Route::get('/pets/{pet}/edit', [PetController::class, 'edit'])->name('pets.edit');
    Route::put('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
    // Limite: máximo 30 uploads de mídia por hora
    Route::post('/pets/{pet}/media', [PetController::class, 'storeMedia'])->middleware('throttle:30,60')->name('pets.media.store');
    Route::delete('/pets/{pet}/media/{media}', [PetController::class, 'destroyMedia'])->name('pets.media.destroy');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    // ── Alerts ───────────────────────────────────────────────────────────────
    Route::post('/alerts', [AlertController::class, 'store'])->middleware('throttle:10,1')->name('alerts.store');
    Route::post('/alerts/test', [AlertController::class, 'testNotification'])->middleware('throttle:10,1')->name('alerts.test');
    // Limite: máximo 5 encerramentos de alerta por minuto (anti-spam)
    Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->middleware('throttle:5,1')->name('alerts.resolve');

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Pet Health - Saúde & Vet ──────────────────────────────────────────────
    Route::get('/pets/{pet}/health', [PetController::class, 'health'])->name('pets.health');
    Route::patch('/pets/{pet}/vet', [PetController::class, 'updateVet'])->name('pets.vet.update');

    // ── Health Records - Fichas Clínicas ──────────────────────────────────────
    Route::post('/pets/{pet}/records', [HealthRecordController::class, 'store'])->name('pets.records.store');
    Route::post('/pets/{pet}/records/{record}/privacy', [HealthRecordController::class, 'updatePrivacy'])->name('pets.records.privacy');
    Route::get('/pets/{pet}/records/{record}/view', [HealthRecordController::class, 'showFile'])->name('pets.records.view');
    Route::delete('/pets/{pet}/records/{record}', [HealthRecordController::class, 'destroy'])->name('pets.records.destroy');

    // ── Schedules - Lembretes de Vacinas/Remédios ─────────────────────────────
    Route::post('/pets/{pet}/schedules', [ScheduleController::class, 'store'])->name('pets.schedules.store');
    Route::post('/pets/{pet}/schedules/{schedule}/toggle', [ScheduleController::class, 'toggle'])->name('pets.schedules.toggle');
    Route::delete('/pets/{pet}/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('pets.schedules.destroy');
});
