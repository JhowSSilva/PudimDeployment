<?php

use App\Http\Controllers\BackupConfigurationController;
use App\Http\Controllers\BackupFileController;
use App\Http\Controllers\BackupJobController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backup Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('backups')->name('backups.')->group(function () {
    
    // Backup Configurations
    Route::get('/', [BackupConfigurationController::class, 'index'])->name('index');
    Route::get('/create', [BackupConfigurationController::class, 'create'])->name('create');
    Route::post('/', [BackupConfigurationController::class, 'store'])->name('store');
    Route::get('/{backup}', [BackupConfigurationController::class, 'show'])->name('show');
    Route::get('/{backup}/edit', [BackupConfigurationController::class, 'edit'])->name('edit');
    Route::put('/{backup}', [BackupConfigurationController::class, 'update'])->name('update');
    Route::delete('/{backup}', [BackupConfigurationController::class, 'destroy'])->name('destroy');
    
    // Backup Actions
    Route::post('/{backup}/run', [BackupConfigurationController::class, 'run'])->name('run');
    Route::post('/{backup}/pause', [BackupConfigurationController::class, 'pause'])->name('pause');
    Route::post('/{backup}/resume', [BackupConfigurationController::class, 'resume'])->name('resume');
    
    // Files & Jobs
    Route::get('/{backup}/files', [BackupFileController::class, 'index'])->name('files');
    Route::get('/{backup}/jobs', [BackupJobController::class, 'index'])->name('jobs');
    Route::get('/files/{file}/download', [BackupFileController::class, 'download'])->name('files.download');
    Route::delete('/files/{file}', [BackupFileController::class, 'destroy'])->name('files.destroy');
});
