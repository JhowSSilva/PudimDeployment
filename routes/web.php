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
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TeamRoleController;
use App\Http\Controllers\ServerPoolController;
use App\Http\Controllers\LoadBalancerController;
use App\Http\Controllers\ScalingPolicyController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PipelineRunController;
use App\Http\Controllers\DeploymentStrategyController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\DeploymentApprovalController;
use App\Http\Controllers\InstanceRegistrationController;
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
    
    // Multi-language server routes
    Route::prefix('servers')->name('servers.')->group(function () {
        Route::get('/create-multi-language', [ServerWebController::class, 'createMultiLanguage'])->name('create-multi-language');
        Route::get('/language-versions/{language}', [ServerWebController::class, 'getLanguageVersions'])->name('language-versions');
        Route::get('{server}/installation-progress', [ServerWebController::class, 'getInstallationProgress'])->name('installation.progress');
        Route::post('{server}/validate-installation', [ServerWebController::class, 'validateInstallation'])->name('installation.validate');

        // Instance registration (manual onboarding)
        Route::post('/registration-tokens', [InstanceRegistrationController::class, 'store'])->name('registration-tokens.store');
        Route::get('/bootstrap.sh', [InstanceRegistrationController::class, 'bootstrapScript'])->name('bootstrap');
    });
    
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

    // Cloud provisioning web endpoints (session-authenticated)
    Route::prefix('cloud/{provider}')->group(function () {
        Route::get('/credentials', [\App\Http\Controllers\CloudProvisionController::class, 'credentials']);
        Route::get('/regions', [\App\Http\Controllers\CloudProvisionController::class, 'regions']);
        Route::get('/instance-types', [\App\Http\Controllers\CloudProvisionController::class, 'instanceTypes']);
        Route::post('/provision', [\App\Http\Controllers\CloudProvisionController::class, 'provision']);
    });
    
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
    
    // Monitoring & Metrics routes
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('index');
        Route::get('/servers/{server}', [MonitoringController::class, 'show'])->name('show');
        Route::post('/servers/{server}/collect', [MonitoringController::class, 'collect'])->name('collect');
        Route::get('/servers/{server}/metrics', [MonitoringController::class, 'metrics'])->name('metrics');
    });
    
    // Alerts routes
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', [AlertController::class, 'index'])->name('index');
        Route::get('/{alert}', [AlertController::class, 'show'])->name('show');
        Route::post('/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->name('acknowledge');
        Route::post('/{alert}/resolve', [AlertController::class, 'resolve'])->name('resolve');
        
        // Alert rules
        Route::get('/rules/index', [AlertController::class, 'rules'])->name('rules');
        Route::get('/rules/create', [AlertController::class, 'createRule'])->name('rules.create');
        Route::post('/rules', [AlertController::class, 'storeRule'])->name('rules.store');
        Route::post('/rules/{rule}/toggle', [AlertController::class, 'toggleRule'])->name('rules.toggle');
        Route::delete('/rules/{rule}', [AlertController::class, 'destroyRule'])->name('rules.destroy');
    });
    
    // Activity Feed routes
    Route::prefix('activity')->name('activity.')->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::get('/resource/{type}/{id}', [ActivityController::class, 'resource'])->name('resource');
    });
    
    // Comments routes
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::post('/', [CommentController::class, 'store'])->name('store');
        Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
        Route::get('/get', [CommentController::class, 'getComments'])->name('get');
    });
    
    // Team Roles routes
    Route::prefix('team/roles')->name('team.roles.')->group(function () {
        Route::get('/', [TeamRoleController::class, 'index'])->name('index');
        Route::get('/create', [TeamRoleController::class, 'create'])->name('create');
        Route::post('/', [TeamRoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [TeamRoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [TeamRoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [TeamRoleController::class, 'destroy'])->name('destroy');
        Route::post('/{role}/assign', [TeamRoleController::class, 'assign'])->name('assign');
        Route::post('/{role}/remove', [TeamRoleController::class, 'remove'])->name('remove');
    });
    
    // Auto-scaling & Load Balancing routes
    Route::prefix('scaling')->name('scaling.')->group(function () {
        // Server Pools
        Route::resource('pools', ServerPoolController::class);
        Route::post('/pools/{pool}/servers/add', [ServerPoolController::class, 'addServer'])->name('pools.servers.add');
        Route::post('/pools/{pool}/servers/remove', [ServerPoolController::class, 'removeServer'])->name('pools.servers.remove');
        
        // Load Balancers
        Route::resource('load-balancers', LoadBalancerController::class);
        Route::get('/load-balancers/{loadBalancer}/stats', [LoadBalancerController::class, 'stats'])->name('load-balancers.stats');
        
        // Scaling Policies
        Route::resource('policies', ScalingPolicyController::class);
        Route::post('/policies/{policy}/toggle', [ScalingPolicyController::class, 'toggle'])->name('policies.toggle');
    });
    
    // CI/CD & Deployment Pipelines routes
    Route::prefix('cicd')->name('cicd.')->group(function () {
        // Pipelines
        Route::resource('pipelines', PipelineController::class);
        Route::post('/pipelines/{pipeline}/pause', [PipelineController::class, 'pause'])->name('pipelines.pause');
        Route::post('/pipelines/{pipeline}/activate', [PipelineController::class, 'activate'])->name('pipelines.activate');
        Route::post('/pipelines/{pipeline}/run', [PipelineController::class, 'run'])->name('pipelines.run');
        
        // Pipeline Runs
        Route::get('/pipelines/{pipeline}/runs', [PipelineRunController::class, 'index'])->name('pipeline-runs.index');
        Route::get('/runs/{pipelineRun}', [PipelineRunController::class, 'show'])->name('pipeline-runs.show');
        Route::post('/runs/{pipelineRun}/cancel', [PipelineRunController::class, 'cancel'])->name('pipeline-runs.cancel');
        Route::post('/runs/{pipelineRun}/retry', [PipelineRunController::class, 'retry'])->name('pipeline-runs.retry');
        Route::get('/runs/{pipelineRun}/logs', [PipelineRunController::class, 'logs'])->name('pipeline-runs.logs');
        Route::delete('/runs/{pipelineRun}', [PipelineRunController::class, 'destroy'])->name('pipeline-runs.destroy');
        
        // Deployment Strategies
        Route::resource('deployment-strategies', DeploymentStrategyController::class);
        Route::post('/deployment-strategies/{deploymentStrategy}/make-default', [DeploymentStrategyController::class, 'makeDefault'])->name('deployment-strategies.make-default');
        
        // Integrations
        Route::resource('integrations', IntegrationController::class);
        Route::post('/integrations/{integration}/toggle', [IntegrationController::class, 'toggle'])->name('integrations.toggle');
        Route::post('/integrations/{integration}/test', [IntegrationController::class, 'test'])->name('integrations.test');
        
        // Deployment Approvals
        Route::get('/approvals', [DeploymentApprovalController::class, 'index'])->name('deployment-approvals.index');
        Route::get('/approvals/{deploymentApproval}', [DeploymentApprovalController::class, 'show'])->name('deployment-approvals.show');
        Route::post('/approvals/{deploymentApproval}/approve', [DeploymentApprovalController::class, 'approve'])->name('deployment-approvals.approve');
        Route::post('/approvals/{deploymentApproval}/reject', [DeploymentApprovalController::class, 'reject'])->name('deployment-approvals.reject');
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
