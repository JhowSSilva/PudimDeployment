<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServerWebController;
use App\Http\Controllers\SiteWebController;
use App\Http\Controllers\CloudflareAccountController;
use App\Http\Controllers\AWSCredentialController;
use App\Http\Controllers\AWSProvisionController;
use App\Http\Controllers\AzureCredentialController;
use App\Http\Controllers\GcpCredentialController;
use App\Http\Controllers\DigitalOceanCredentialController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TeamSwitchController;
use App\Http\Controllers\SSLController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\QueueWorkerController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\FileTransferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\UsageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Health check endpoints (no auth)
Route::get('/health', [HealthCheckController::class, 'check'])->name('health.check');
Route::get('/ping', [HealthCheckController::class, 'ping'])->name('health.ping');

// Webhook routes (no auth middleware - called by external services)
Route::prefix('webhooks')->name('webhooks.')->middleware('throttle:webhooks')->group(function () {
    Route::post('/receive/{siteId}/{token}', [WebhookController::class, 'receive'])->name('receive');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('servers', ServerWebController::class);
    Route::resource('sites', SiteWebController::class);
    Route::get('servers/{server}/sites/create', [SiteWebController::class, 'createForServer'])->name('servers.sites.create');
    
    // Terminal routes (standalone)
    Route::prefix('terminal')->name('terminal.')->group(function () {
        Route::get('/', [TerminalController::class, 'index'])->name('index');
        Route::get('/{server}', [TerminalController::class, 'show'])->name('show');
        Route::post('/{server}/execute', [TerminalController::class, 'execute'])->name('execute');
        Route::post('/{server}/stream', [TerminalController::class, 'stream'])->name('stream');
    });
    
    // Terminal routes (legacy - server context)
    Route::prefix('servers/{server}/terminal')->name('servers.terminal.')->group(function () {
        Route::get('/', [TerminalController::class, 'show'])->name('show');
        Route::post('/execute', [TerminalController::class, 'execute'])->name('execute');
        Route::post('/stream', [TerminalController::class, 'stream'])->name('stream');
        Route::get('/info', [TerminalController::class, 'info'])->name('info');
    });

    // File Transfer routes (upload/download arquivos via SSH)
    Route::prefix('servers/{server}/files')->name('files.')->group(function () {
        Route::post('/upload', [FileTransferController::class, 'upload'])->name('upload');
        Route::post('/download', [FileTransferController::class, 'download'])->name('download');
        Route::get('/list', [FileTransferController::class, 'list'])->name('list');
        Route::delete('/delete', [FileTransferController::class, 'delete'])->name('delete');
    });
    
    Route::resource('cloudflare-accounts', CloudflareAccountController::class);
    
    // AWS Credentials
    Route::resource('aws-credentials', AWSCredentialController::class);
    
    // AWS Provision Wizard
    Route::get('/aws/provision/step1', [AWSProvisionController::class, 'step1'])->name('aws-provision.step1');
    Route::post('/aws/provision/step2', [AWSProvisionController::class, 'step2'])->name('aws-provision.step2');
    Route::post('/aws/provision/step3', [AWSProvisionController::class, 'step3'])->name('aws-provision.step3');
    Route::post('/aws/provision/step4', [AWSProvisionController::class, 'step4'])->name('aws-provision.step4');
    Route::post('/aws/provision', [AWSProvisionController::class, 'provision'])->name('aws-provision.provision');
    
    // Azure Credentials
    Route::resource('azure-credentials', AzureCredentialController::class);
    
    // GCP Credentials
    Route::resource('gcp-credentials', GcpCredentialController::class);
    
    // DigitalOcean Credentials
    Route::resource('digitalocean-credentials', DigitalOceanCredentialController::class);
    
    // SSL Management
    Route::prefix('sites/{site}/ssl')->name('ssl.')->group(function () {
        Route::get('/', [SSLController::class, 'show'])->name('show');
        Route::post('/letsencrypt', [SSLController::class, 'enableLetsEncrypt'])->name('letsencrypt');
        Route::post('/custom', [SSLController::class, 'uploadCustom'])->name('custom');
        Route::post('/https-redirect', [SSLController::class, 'enableHttpsRedirect'])->name('https-redirect.enable');
        Route::delete('/https-redirect', [SSLController::class, 'disableHttpsRedirect'])->name('https-redirect.disable');
    });
    
    Route::prefix('ssl-certificates/{certificate}')->name('ssl-certificates.')->group(function () {
        Route::post('/renew', [SSLController::class, 'renewCertificate'])->name('renew');
        Route::delete('/', [SSLController::class, 'deleteCertificate'])->name('delete');
        Route::get('/info', [SSLController::class, 'getCertificateInfo'])->name('info');
    });
    
    // Database Management
    Route::get('/databases', [DatabaseController::class, 'globalIndex'])->name('databases.index');
    Route::prefix('servers/{server}/databases')->name('servers.databases.')->group(function () {
        Route::get('/', [DatabaseController::class, 'index'])->name('index');
        Route::get('/create', [DatabaseController::class, 'create'])->name('create');
        Route::post('/', [DatabaseController::class, 'store'])->name('store');
        Route::get('/{database}', [DatabaseController::class, 'show'])->name('show');
        Route::delete('/{database}', [DatabaseController::class, 'destroy'])->name('destroy');
        Route::post('/sync', [DatabaseController::class, 'sync'])->name('sync');
        
        Route::prefix('/{database}/users')->name('users.')->group(function () {
            Route::post('/', [DatabaseController::class, 'createUser'])->name('create');
            Route::delete('/{user}', [DatabaseController::class, 'deleteUser'])->name('delete');
        });
        
        Route::post('/{database}/backup', [DatabaseController::class, 'backup'])->name('backup');
    });
    
    // Queue Worker Management
    Route::get('/queue-workers', [QueueWorkerController::class, 'globalIndex'])->name('queue-workers.index');
    Route::prefix('servers/{server}/queue-workers')->name('servers.queue-workers.')->group(function () {
        Route::get('/', [QueueWorkerController::class, 'index'])->name('index');
        Route::get('/create', [QueueWorkerController::class, 'create'])->name('create');
        Route::post('/', [QueueWorkerController::class, 'store'])->name('store');
        Route::get('/{worker}', [QueueWorkerController::class, 'show'])->name('show');
        Route::delete('/{worker}', [QueueWorkerController::class, 'destroy'])->name('destroy');
        Route::post('/{worker}/stop', [QueueWorkerController::class, 'stop'])->name('stop');
        Route::post('/{worker}/restart', [QueueWorkerController::class, 'restart'])->name('restart');
        Route::post('/restart-all', [QueueWorkerController::class, 'restartAll'])->name('restart-all');
        Route::post('/retry-failed', [QueueWorkerController::class, 'retryFailedJobs'])->name('retry-failed');
        Route::post('/clear-failed', [QueueWorkerController::class, 'clearFailedJobs'])->name('clear-failed');
        
        // API routes for real-time data
        Route::get('/{worker}/logs', [QueueWorkerController::class, 'logs'])->name('logs');
        Route::get('/failed-jobs', [QueueWorkerController::class, 'failedJobs'])->name('failed-jobs');
        Route::get('/stats', [QueueWorkerController::class, 'stats'])->name('stats');
    });
    
    // SSL Certificates Management
    Route::get('/ssl-certificates', [SSLController::class, 'globalIndex'])->name('ssl-certificates.index');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
    });
    
    // Webhook Management (authenticated)
    Route::prefix('sites/{site}/webhooks')->name('sites.webhooks.')->group(function () {
        Route::get('/config', [WebhookController::class, 'config'])->name('config');
        Route::post('/enable', [WebhookController::class, 'enable'])->name('enable');
        Route::post('/disable', [WebhookController::class, 'disable'])->name('disable');
        Route::post('/regenerate-secret', [WebhookController::class, 'regenerateSecret'])->name('regenerate-secret');
    });
    
    // Billing & Subscription routes
    Route::prefix('billing')->name('billing.')->group(function () {
        // Plans routes (public within auth)
        Route::get('/plans', [PlansController::class, 'index'])->name('plans');
        Route::get('/plans/{plan}', [PlansController::class, 'show'])->name('plans.show');
        
        // Subscription management
        Route::get('/subscription', [SubscriptionsController::class, 'show'])->name('subscription');
        Route::post('/subscribe/{plan}', [SubscriptionsController::class, 'subscribe'])->name('subscribe');
        Route::post('/subscription/cancel', [SubscriptionsController::class, 'cancel'])->name('subscription.cancel');
        Route::post('/subscription/resume', [SubscriptionsController::class, 'resume'])->name('subscription.resume');
        Route::post('/subscription/swap/{plan}', [SubscriptionsController::class, 'swap'])->name('subscription.swap');
        
        // Usage metrics
        Route::get('/usage', [UsageController::class, 'index'])->name('usage');
    });
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Team management routes
    Route::post('/profile/teams', [ProfileController::class, 'createTeam'])->name('profile.teams.create');
    Route::delete('/profile/teams/{team}', [ProfileController::class, 'deleteTeam'])->name('profile.teams.delete');
    Route::post('/teams/{team}/invite', [ProfileController::class, 'inviteToTeam'])->name('teams.invite');
    Route::put('/teams/{team}/members/{user}', [ProfileController::class, 'updateTeamMemberRole'])->name('teams.members.update');
    Route::delete('/teams/{team}/members/{user}', [ProfileController::class, 'removeTeamMember'])->name('teams.members.remove');
    Route::get('/teams/{team}', [ProfileController::class, 'showTeam'])->name('teams.show');
    
    // Team switching
    Route::post('/teams/{team}/switch', [TeamSwitchController::class, 'switch'])->name('teams.switch');
});

// Team invitation routes (public - no auth required)
Route::get('/invites/{token}', [TeamInvitationController::class, 'show'])->name('invites.show');
Route::post('/invites/{token}/accept', [TeamInvitationController::class, 'accept'])->name('invites.accept');
Route::delete('/invites/{token}', [TeamInvitationController::class, 'reject'])->name('invites.reject');

// Team invitation management (auth required)
Route::middleware(['auth'])->group(function () {
    Route::post('/invitations/{invitation}/resend', [TeamInvitationController::class, 'resend'])->name('invitations.resend');
    Route::delete('/invitations/{invitation}', [TeamInvitationController::class, 'cancel'])->name('invitations.cancel');
});

// GitHub Integration Routes
require __DIR__.'/github.php';

// Backup System Routes
require __DIR__.'/backups.php';

require __DIR__.'/auth.php';
