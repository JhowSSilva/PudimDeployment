<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerManagementTest extends TestCase
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

    public function test_user_can_view_servers_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('servers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('servers.index');
    }

    public function test_user_can_view_create_server_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('servers.create'));

        $response->assertStatus(200);
        $response->assertViewIs('servers.create');
    }

    public function test_user_can_create_server(): void
    {
        $serverData = [
            'name' => 'Test Server',
            'ip_address' => '192.168.1.100',
            'ssh_port' => 22,
            'ssh_user' => 'ubuntu',
            'provider' => 'digitalocean',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('servers.store'), $serverData);

        $response->assertRedirect();
        $this->assertDatabaseHas('servers', [
            'name' => 'Test Server',
            'ip_address' => '192.168.1.100',
            'team_id' => $this->team->id,
        ]);
    }

    public function test_user_can_view_own_team_server(): void
    {
        $server = Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('servers.show', $server));

        $response->assertStatus(200);
        $response->assertViewIs('servers.show');
    }

    public function test_user_cannot_view_other_team_server(): void
    {
        $otherTeam = Team::factory()->create();
        $otherServer = Server::factory()->create([
            'team_id' => $otherTeam->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('servers.show', $otherServer));

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_team_server(): void
    {
        $server = Server::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('servers.update', $server), [
                'name' => 'New Name',
                'ip_address' => $server->ip_address,
                'ssh_port' => $server->ssh_port,
                'ssh_user' => $server->ssh_user,
                'provider' => $server->provider,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('servers', [
            'id' => $server->id,
            'name' => 'New Name',
        ]);
    }

    public function test_user_cannot_update_other_team_server(): void
    {
        $otherTeam = Team::factory()->create();
        $otherServer = Server::factory()->create([
            'team_id' => $otherTeam->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('servers.update', $otherServer), [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('servers', [
            'id' => $otherServer->id,
            'name' => 'Hacked Name',
        ]);
    }

    public function test_user_can_delete_own_team_server(): void
    {
        $server = Server::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('servers.destroy', $server));

        $response->assertRedirect();
        $this->assertSoftDeleted('servers', [
            'id' => $server->id,
        ]);
    }

    public function test_user_cannot_delete_other_team_server(): void
    {
        $otherTeam = Team::factory()->create();
        $otherServer = Server::factory()->create([
            'team_id' => $otherTeam->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('servers.destroy', $otherServer));

        $response->assertStatus(403);
        $this->assertDatabaseHas('servers', [
            'id' => $otherServer->id,
            'deleted_at' => null,
        ]);
    }

    public function test_server_creation_requires_valid_ip(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('servers.store'), [
                'name' => 'Test Server',
                'ip_address' => 'invalid-ip',
                'ssh_port' => 22,
                'ssh_user' => 'ubuntu',
                'provider' => 'digitalocean',
            ]);

        $response->assertSessionHasErrors('ip_address');
    }

    public function test_server_creation_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('servers.store'), [
                'ip_address' => '192.168.1.100',
                'ssh_port' => 22,
                'ssh_user' => 'ubuntu',
                'provider' => 'digitalocean',
            ]);

        $response->assertSessionHasErrors('name');
    }
}
