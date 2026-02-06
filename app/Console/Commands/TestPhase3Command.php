<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Team;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestPhase3Command extends Command
{
    protected $signature = 'test:phase3';
    protected $description = 'Test Phase 3 features (Webhooks, Terminal, Notifications)';

    public function handle(NotificationService $notificationService)
    {
        $this->info('🚀 Testing Phase 3 Features...');
        $this->newLine();

        // Find test user and team
        $user = User::find(44);
        $team = Team::find(59);

        if (!$user || !$team) {
            $this->error('Test user (ID: 44) or team (ID: 59) not found!');
            return Command::FAILURE;
        }

        $this->info("Testing with User: {$user->name} (ID: {$user->id})");
        $this->info("Testing with Team: {$team->name} (ID: {$team->id})");
        $this->newLine();

        // Test 1: Create notification
        $this->line('📬 Test 1: Creating notification...');
        try {
            $notification = $notificationService->create(
                user: $user,
                team: $team,
                type: 'success',
                title: '🎉 Fase 3 Concluída!',
                message: 'Sistema de Webhooks, Terminal Web e Notificações implementado com sucesso.',
                actionUrl: route('dashboard'),
                actionText: 'Ver Dashboard'
            );
            $this->info("   ✓ Notification created (ID: {$notification->id})");
        } catch (\Exception $e) {
            $this->error("   ✗ Failed: {$e->getMessage()}");
        }

        // Test 2: Get unread notifications
        $this->newLine();
        $this->line('📋 Test 2: Getting unread notifications...');
        try {
            $unread = $notificationService->getUnread($user, 10);
            $this->info("   ✓ Found {$unread->count()} unread notifications");
            
            if ($unread->count() > 0) {
                $this->newLine();
                $this->line('   Recent notifications:');
                foreach ($unread->take(3) as $notif) {
                    $this->line("   • {$notif->title} - {$notif->message}");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Failed: {$e->getMessage()}");
        }

        // Test 3: Create deployment notification
        $this->newLine();
        $this->line('🚀 Test 3: Creating deployment notification...');
        try {
            $server = $team->servers()->first();
            $site = $server?->sites()->first();
            
            if ($site) {
                $notificationService->deployment(
                    user: $user,
                    team: $team,
                    site: $site,
                    status: 'success',
                    message: "Deploy de teste concluído para {$site->domain}",
                    actionUrl: route('sites.show', $site)
                );
                $this->info("   ✓ Deployment notification created for {$site->domain}");
            } else {
                $this->warn('   ⚠ No site found to test deployment notification');
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Failed: {$e->getMessage()}");
        }

        // Test 4: Create security notification
        $this->newLine();
        $this->line('🔒 Test 4: Creating security notification...');
        try {
            $server = $team->servers()->first();
            
            if ($server) {
                $notificationService->security(
                    user: $user,
                    serverName: $server->name,
                    threat: 'Teste de segurança: 3 rootkits detectados',
                    url: route('servers.show', $server)
                );
                $this->info("   ✓ Security notification created for {$server->name}");
            } else {
                $this->warn('   ⚠ No server found to test security notification');
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Failed: {$e->getMessage()}");
        }

        $this->newLine();
        $this->info('✅ Phase 3 testing completed!');
        $this->newLine();
        $this->line('Visit http://localhost:8000 and check:');
        $this->line('  • Notification bell in header (should show badge with unread count)');
        $this->line('  • /notifications page for all notifications');
        $this->line('  • /servers/{id}/terminal for web terminal');
        $this->line('  • Site webhook configuration');
        
        return Command::SUCCESS;
    }
}
