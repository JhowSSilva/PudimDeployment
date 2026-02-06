<?php

use App\Http\Controllers\GitHubAuthController;
use App\Http\Controllers\GitHubPagesController;
use App\Http\Controllers\GitHubRepositoryController;
use App\Http\Controllers\GitHubWebhookController;
use App\Http\Controllers\GitHubWorkflowController;
use App\Http\Middleware\EnsureGitHubTokenValid;
use Illuminate\Support\Facades\Route;

// GitHub Webhook (no auth required)
Route::post('/webhook/github', [GitHubWebhookController::class, 'handle'])->name('github.webhook');
Route::get('/webhook/github/test', [GitHubWebhookController::class, 'test'])->name('github.webhook.test');

// GitHub OAuth Routes
Route::prefix('github')->name('github.')->group(function () {
    Route::get('/settings', [GitHubAuthController::class, 'settings'])->name('settings');
    Route::get('/connect', [GitHubAuthController::class, 'redirectToGitHub'])->name('connect');
    Route::get('/callback', [GitHubAuthController::class, 'handleGitHubCallback'])->name('callback');
    Route::post('/disconnect', [GitHubAuthController::class, 'disconnect'])->name('disconnect');
    Route::post('/personal-token', [GitHubAuthController::class, 'savePersonalToken'])->name('personal-token');
    
    // Protected routes (require GitHub token)
    Route::middleware([EnsureGitHubTokenValid::class])->group(function () {
        
        // Repositories
        Route::prefix('repositories')->name('repositories.')->group(function () {
            Route::get('/', [GitHubRepositoryController::class, 'index'])->name('index');
            Route::post('/sync', [GitHubRepositoryController::class, 'sync'])->name('sync');
            Route::get('/{repository}', [GitHubRepositoryController::class, 'show'])->name('show');
            Route::post('/{repository}/webhook', [GitHubRepositoryController::class, 'setupWebhook'])->name('webhook');
            
            // Workflows
            Route::prefix('{repository}/workflows')->name('workflows.')->group(function () {
                Route::get('/', [GitHubWorkflowController::class, 'index'])->name('index');
                Route::post('/sync', [GitHubWorkflowController::class, 'sync'])->name('sync');
                Route::post('/{workflow}/dispatch', [GitHubWorkflowController::class, 'dispatch'])->name('dispatch');
                Route::post('/runs/{run}/cancel', [GitHubWorkflowController::class, 'cancel'])->name('cancel');
                Route::post('/runs/{run}/rerun', [GitHubWorkflowController::class, 'rerun'])->name('rerun');
            });
            
            // GitHub Pages
            Route::prefix('{repository}/pages')->name('pages.')->group(function () {
                Route::get('/', [GitHubPagesController::class, 'show'])->name('show');
                Route::post('/enable', [GitHubPagesController::class, 'enable'])->name('enable');
                Route::post('/disable', [GitHubPagesController::class, 'disable'])->name('disable');
                Route::put('/update', [GitHubPagesController::class, 'update'])->name('update');
                Route::post('/build', [GitHubPagesController::class, 'requestBuild'])->name('build');
            });
        });
        
        // Workflow Templates
        Route::get('/workflow-templates', [GitHubWorkflowController::class, 'templates'])->name('workflow.templates');
    });
});
