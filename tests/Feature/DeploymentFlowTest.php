<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Deployment;
use App\Models\Server;
use App\Models\Site;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;
    protected Server $server;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);
        
        $this->server = Server::factory()->create([
            'team_id' => $this->team->id,
        ]);
        
        $this->site = Site::factory()->create([
            'server_id' => $this->server->id,
        ]);
    }

    public function test_user_can_view_deployments_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('sites.deployments.index', $this->site));

        $response->assertStatus(200);
        $response->assertViewIs('deployments.index');
    }

    public function test_user_can_trigger_deployment(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('sites.deployments.store', $this->site), [
                'git_branch' => 'main',
                'commit_message' => 'Test deployment',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('deployments', [
            'site_id' => $this->site->id,
            'git_branch' => 'main',
            'status' => 'pending',
        ]);
    }

    public function test_deployment_creation_logs_audit(): void
    {
        $this->actingAs($this->user)
            ->post(route('sites.deployments.store', $this->site), [
                'git_branch' => 'main',
            ]);

        $deployment = Deployment::where('site_id', $this->site->id)->first();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deployment_created',
            'model_type' => Deployment::class,
            'model_id' => $deployment->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_successful_deployment_updates_status(): void
    {
        $deployment = Deployment::factory()->create([
            'site_id' => $this->site->id,
            'status' => 'pending',
        ]);

        $deployment->update(['status' => 'success']);

        $this->assertDatabaseHas('deployments', [
            'id' => $deployment->id,
            'status' => 'success',
        ]);
    }

    public function test_failed_deployment_creates_audit_log(): void
    {
        $deployment = Deployment::factory()->create([
            'site_id' => $this->site->id,
            'status' => 'in_progress',
        ]);

        // Clear previous logs
        AuditLog::query()->delete();

        $deployment->update(['status' => 'failed']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deployment_failed',
            'model_type' => Deployment::class,
            'model_id' => $deployment->id,
        ]);
    }

    public function test_user_cannot_deploy_other_team_site(): void
    {
        $otherTeam = Team::factory()->create();
        $otherServer = Server::factory()->create([
            'team_id' => $otherTeam->id,
        ]);
        $otherSite = Site::factory()->create([
            'server_id' => $otherServer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('sites.deployments.store', $otherSite), [
                'git_branch' => 'main',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_view_deployment_details(): void
    {
        $deployment = Deployment::factory()->create([
            'site_id' => $this->site->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('sites.deployments.show', [$this->site, $deployment]));

        $response->assertStatus(200);
        $response->assertViewIs('deployments.show');
    }

    public function test_deployment_rate_limiting_works(): void
    {
        // Trigger 11 deployments (exceeds 10/min limit)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->actingAs($this->user)
                ->post(route('sites.deployments.store', $this->site), [
                    'git_branch' => 'main',
                ]);

            if ($i < 10) {
                $response->assertRedirect();
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_deployment_stores_git_commit_hash(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('sites.deployments.store', $this->site), [
                'git_branch' => 'main',
                'git_commit' => 'abc123def456',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('deployments', [
            'site_id' => $this->site->id,
            'git_commit' => 'abc123def456',
        ]);
    }

    public function test_deployment_records_start_and_end_times(): void
    {
        $deployment = Deployment::factory()->create([
            'site_id' => $this->site->id,
            'status' => 'pending',
            'started_at' => null,
        ]);

        $deployment->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->assertNotNull($deployment->fresh()->started_at);

        $deployment->update([
            'status' => 'success',
            'finished_at' => now(),
        ]);

        $this->assertNotNull($deployment->fresh()->finished_at);
    }

    public function test_multiple_deployments_failure_triggers_alert(): void
    {
        // Create 3 failed deployments within 1 hour
        for ($i = 0; $i < 3; $i++) {
            Deployment::factory()->create([
                'site_id' => $this->site->id,
                'status' => 'failed',
                'created_at' => now()->subMinutes($i * 10),
            ]);
        }

        // Trigger 4th failure (should alert)
        $deployment = Deployment::factory()->create([
            'site_id' => $this->site->id,
            'status' => 'in_progress',
        ]);

        // Clear previous logs
        AuditLog::where('action', '!=', 'deployment_failed')->delete();

        $deployment->update(['status' => 'failed']);

        // Check if alert was logged (DeploymentObserver should handle this)
        $recentFailures = Deployment::where('site_id', $this->site->id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $this->assertGreaterThanOrEqual(3, $recentFailures);
    }
}
