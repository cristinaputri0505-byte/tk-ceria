<?php

use App\Http\Controllers\Admin\ResetPasswordController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LupaPasswordController;
use App\Http\Controllers\NotifikasiController;
use Illuminate\Support\Facades\Route;

/* Lupa password (tamu): mengirim permintaan ke admin */
Route::middleware('guest')->group(function () {
    Route::get('lupa-password', [LupaPasswordController::class, 'form'])->name('lupa.form');
    Route::post('lupa-password', [LupaPasswordController::class, 'kirim'])->middleware('throttle:5,1')->name('lupa.kirim');
});

/* Admin: memproses permintaan reset password */
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('reset-password', [ResetPasswordController::class, 'index'])->name('reset.index');
    Route::post('reset-password/{permintaan}/rilis', [ResetPasswordController::class, 'rilis'])->name('reset.rilis');
    Route::post('reset-password/{permintaan}/tolak', [ResetPasswordController::class, 'tolak'])->name('reset.tolak');
});

/* Notifikasi: semua peran yang sudah login */
Route::middleware('auth')->prefix('notifikasi')->name('notifikasi.')->group(function () {
    Route::get('/', [NotifikasiController::class, 'index'])->name('index');
    Route::get('ringkas', [NotifikasiController::class, 'ringkas'])->name('ringkas');
    Route::post('baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('baca-semua');
    Route::get('{notifikasi}/buka', [NotifikasiController::class, 'buka'])->name('buka');
});

/* Chat: admin dan orang tua */
Route::middleware(['auth', 'role:admin,orangtua'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('pribadi', [ChatController::class, 'pribadi'])->name('pribadi');
    Route::get('pribadi/data', [ChatController::class, 'pribadiData'])->name('pribadi.data');
    Route::post('pribadi', [ChatController::class, 'pribadiKirim'])->middleware('throttle:40,1')->name('pribadi.kirim');
    Route::get('grup', [ChatController::class, 'grup'])->name('grup');
    Route::get('grup/data', [ChatController::class, 'grupData'])->name('grup.data');
    Route::post('grup', [ChatController::class, 'grupKirim'])->middleware('throttle:40,1')->name('grup.kirim');
});
