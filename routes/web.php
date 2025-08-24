<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;


Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth'])->group(function () {
    
    // Report routes
    Route::prefix('reports')->name('reports.')->group(function () {
        
        // Download laporan keluarga
        Route::get('/keluarga', [ReportController::class, 'downloadKeluargaReport'])
            ->name('keluarga');
        
        // Download laporan agenda kegiatan
        Route::get('/agenda', [ReportController::class, 'downloadAgendaReport'])
            ->name('agenda');
        
        // Download laporan kunjungan rumah
        Route::get('/kunjungan', [ReportController::class, 'downloadKunjunganReport'])
            ->name('kunjungan');
        
        // Download laporan rekapitulasi
        Route::get('/rekapitulasi', [ReportController::class, 'downloadRekapitulasiReport'])
            ->name('rekapitulasi');
    });
});