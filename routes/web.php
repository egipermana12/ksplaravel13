<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\Api\AnggotaApiController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\SimpananController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::delete('anggota/bulk', [AnggotaController::class, 'bulkDestroy'])
        ->name('anggota.bulk-destroy');

    Route::resource('anggota', AnggotaController::class)
        ->parameters(['anggota' => 'anggota'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('simpanan', SimpananController::class)
        ->parameters(['simpanan' => 'simpanan'])
        ->only(['index', 'create', 'store']);

    Route::patch('pinjaman/{pinjaman}/approve', [PinjamanController::class, 'approve'])
        ->name('pinjaman.approve');

    Route::resource('pinjaman', PinjamanController::class)
        ->parameters(['pinjaman' => 'pinjaman'])
        ->only(['index', 'create', 'store']);

    Route::resource('jurnals', JurnalController::class)
        ->parameters(['jurnals' => 'jurnals'])
        ->only(['index']);

    Route::middleware(['auth'])->group(function () {
        Route::get('/ajax/anggota', [AnggotaApiController::class, 'index']);
    });
});

require __DIR__.'/settings.php';
