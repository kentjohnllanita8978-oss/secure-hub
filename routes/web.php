<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ── Landing page ──────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── Auth routes ───────────────────────────────────────────────────────────────
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register']);

Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// ── Protected routes ──────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

// ── REST API endpoints for React Native mobile client ─────────────────────────
Route::prefix('api')->group(function () {
    Route::get('/status', function () {
        return response()->json([
            'status'       => 'Operational',
            'protection'   => 'ThreatShield Mobile v2.4',
            'splash_delay' => '5.0 Seconds',
            'backend'      => 'PHP Laravel 12 API',
            'timestamp'    => now()->toIso8601String(),
        ]);
    });

    Route::get('/health', function () {
        return response()->json([
            'healthy' => true,
            'service' => 'SecureHub ThreatShield API',
        ]);
    });
});
