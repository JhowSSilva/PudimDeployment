<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Server;
use App\Models\Site;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_server_creation_is_logged(): void
    {
        $this->actingAs($this->user);

        $server = Server::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Test Server',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'action' => 'created',
            'model_type' => Server::class,
            'model_id' => $server->id,
        ]);

        $log = AuditLog::where('model_id', $server->id)
            ->where('model_type', Server::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('created', $log->action);
        $this->assertNotNull($log->metadata);
        $this->assertEquals('Test Server', $log->metadata['server_name']);
    }

    public function test_server_update_is_logged_with_changes(): void
    {
        $this->actingAs($this->user);

        $server = Server::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Old Name',
        ]);

        // Clear previous logs
        AuditLog::query()->delete();

        $server->update(['name' => 'New Name']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated',
            'model_type' => Server::class,
            'model_id' => $server->id,
        ]);

        $log = AuditLog::where('model_id', $server->id)
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($log->changes);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertEquals('New Name', $log->changes['name']);
    }

    public function test_server_deletion_is_logged(): void
    {
        $this->actingAs($this->user);

        $server = Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $serverId = $server->id;
        
        // Clear previous logs
        AuditLog::query()->delete();

        $server->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'model_type' => Server::class,
            'model_id' => $serverId,
        ]);
    }

    public function test_site_creation_is_logged(): void
    {
        $this->actingAs($this->user);

        $server = Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $site = Site::factory()->create([
            'server_id' => $server->id,
            'name' => 'Test Site',
            'domain' => 'test.com',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'model_type' => Site::class,
            'model_id' => $site->id,
        ]);

        $log = AuditLog::where('model_id', $site->id)
            ->where('model_type', Site::class)
            ->first();

        $this->assertNotNull($log->metadata);
        $this->assertEquals('Test Site', $log->metadata['site_name']);
        $this->assertEquals('test.com', $log->metadata['domain']);
    }

    public function test_audit_log_captures_ip_and_user_agent(): void
    {
        $this->actingAs($this->user);

        $server = Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $log = AuditLog::where('model_id', $server->id)->first();

        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_can_filter_logs_by_team(): void
    {
        $this->actingAs($this->user);

        // Create server for current team
        Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        // Create server for other team
        $otherTeam = Team::factory()->create();
        Server::factory()->create([
            'team_id' => $otherTeam->id,
        ]);

        $teamLogs = AuditLog::forTeam($this->team->id)->get();
        $otherTeamLogs = AuditLog::forTeam($otherTeam->id)->get();

        $this->assertGreaterThan(0, $teamLogs->count());
        $this->assertGreaterThan(0, $otherTeamLogs->count());
        
        // Verify each log belongs to its respective team
        $this->assertTrue($teamLogs->every(fn($log) => $log->team_id === $this->team->id));
        $this->assertTrue($otherTeamLogs->every(fn($log) => $log->team_id === $otherTeam->id));
    }

    public function test_can_filter_logs_by_action(): void
    {
        $this->actingAs($this->user);

        $server = Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $server->update(['name' => 'Updated']);

        $createdLogs = AuditLog::action('created')->get();
        $updatedLogs = AuditLog::action('updated')->get();

        $this->assertTrue($createdLogs->every(fn($log) => $log->action === 'created'));
        $this->assertTrue($updatedLogs->every(fn($log) => $log->action === 'updated'));
    }

    public function test_can_get_recent_logs(): void
    {
        $this->actingAs($this->user);

        Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $recentLogs = AuditLog::recent(7)->get();

        $this->assertGreaterThan(0, $recentLogs->count());
        
        // All logs should be within last 7 days
        $sevenDaysAgo = now()->subDays(7);
        $this->assertTrue($recentLogs->every(fn($log) => $log->created_at >= $sevenDaysAgo));
    }

    public function test_audit_log_description_is_human_readable(): void
    {
        $this->actingAs($this->user);

        $server = Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $log = AuditLog::where('model_id', $server->id)->first();

        $this->assertNotNull($log->description);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString('created', strtolower($log->description));
        $this->assertStringContainsString('Server', $log->description);
    }
}
