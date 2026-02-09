<?php

use App\Http\Controllers\SSHTerminalController;
use App\Http\Controllers\SSHKeyController;
use Illuminate\Support\Facades\Route;

// Rotas de Terminal SSH (requerem autenticação)
Route::middleware(['auth', 'verified'])->group(function () {
    // Views
    Route::get('/ssh/terminal', [SSHTerminalController::class, 'index'])->name('ssh.terminal');
    Route::get('/ssh/keys', [SSHTerminalController::class, 'keys'])->name('ssh.keys');
    
    // API - Chaves SSH
    Route::prefix('api/ssh/keys')->group(function () {
        Route::get('/', [SSHKeyController::class, 'index']);
        Route::post('/generate', [SSHKeyController::class, 'generate']);
        Route::post('/import', [SSHKeyController::class, 'import']);
        Route::get('/{keyId}/public', [SSHKeyController::class, 'getPublicKey']);
        Route::delete('/{keyId}', [SSHKeyController::class, 'destroy']);
    });
    
    // API - Logs
    Route::get('/api/ssh/logs', [SSHTerminalController::class, 'getLogs']);
});
