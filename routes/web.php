<?php

use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SwitchRoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/berita/{announcement}', [LandingController::class, 'berita'])->name('berita.show');

Route::post('impersonate/stop', [ImpersonationController::class, 'stop'])
    ->middleware('auth')
    ->name('impersonate.stop');

/** Ganti "role aktif" untuk akun multi-role (lihat User::activeRole()). */
Route::get('switch-role/{role}', SwitchRoleController::class)
    ->middleware('auth')
    ->name('switch-role');

/*
| Unduhan berkas privat. Butuh login; otorisasi per-usulan ada di controller.
*/
Route::middleware('auth')->prefix('berkas')->name('download.')->group(function (): void {
    Route::get('usulan/{proposal}', [FileDownloadController::class, 'proposal'])->name('proposal');
    Route::get('laporan/{report}', [FileDownloadController::class, 'report'])->name('report');
    Route::get('luaran/{output}', [FileDownloadController::class, 'output'])->name('output');
    Route::get('sk/{funding}', [FileDownloadController::class, 'sk'])->name('sk');
});
