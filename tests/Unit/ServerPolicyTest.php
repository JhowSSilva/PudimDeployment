<?php

namespace Tests\Unit;

use App\Models\Server;
use App\Models\Team;
use App\Models\User;
use App\Policies\ServerPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ServerPolicy $policy;
    protected User $owner;
    protected User $teamMember;
    protected User $outsider;
    protected Team $team;
    protected Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ServerPolicy();

        // Create team owner
        $this->owner = User::factory()->create();
        
        // Create team
        $this->team = Team::create([
            'user_id' => $this->owner->id,
            'name' => 'Test Team',
            'personal_team' => false,
        ]);

        // Add owner to team
        $this->team->users()->attach($this->owner->id, ['role' => 'admin']);
        $this->owner->update(['current_team_id' => $this->team->id]);

        // Create team member
        $this->teamMember = User::factory()->create();
        $this->team->users()->attach($this->teamMember->id, ['role' => 'member']);
        $this->teamMember->update(['current_team_id' => $this->team->id]);

        // Create outsider (different team)
        $this->outsider = User::factory()->create();
        $outsiderTeam = Team::create([
            'user_id' => $this->outsider->id,
            'name' => 'Outsider Team',
            'personal_team' => false,
        ]);
        $outsiderTeam->users()->attach($this->outsider->id, ['role' => 'admin']);
        $this->outsider->update(['current_team_id' => $outsiderTeam->id]);

        // Create server for team
        $this->server = Server::create([
            'team_id' => $this->team->id,
            'user_id' => $this->owner->id,
            'name' => 'Test Server',
            'ip_address' => '192.168.1.100',
            'ssh_port' => 22,
            'ssh_user' => 'root',
            'status' => 'provisioning',
        ]);
    }

    /** @test */
    public function team_member_can_view_team_server()
    {
        $this->assertTrue(
            $this->policy->view($this->teamMember, $this->server),
            'Team member should be able to view team server'
        );
    }

    /** @test */
    public function outsider_cannot_view_other_team_server()
    {
        $this->assertFalse(
            $this->policy->view($this->outsider, $this->server),
            'Outsider should NOT be able to view other team server'
        );
    }

    /** @test */
    public function team_owner_can_update_server()
    {
        $this->assertTrue(
            $this->policy->update($this->owner, $this->server),
            'Team owner should be able to update server'
        );
    }

    /** @test */
    public function team_member_can_update_server()
    {
        $this->assertTrue(
            $this->policy->update($this->teamMember, $this->server),
            'Team member with create-resources permission should be able to update server'
        );
    }

    /** @test */
    public function outsider_cannot_update_other_team_server()
    {
        $this->assertFalse(
            $this->policy->update($this->outsider, $this->server),
            'Outsider should NOT be able to update other team server'
        );
    }

    /** @test */
    public function team_owner_can_delete_server()
    {
        $this->assertTrue(
            $this->policy->delete($this->owner, $this->server),
            'Team owner should be able to delete server'
        );
    }

    /** @test */
    public function outsider_cannot_delete_other_team_server()
    {
        $this->assertFalse(
            $this->policy->delete($this->outsider, $this->server),
            'Outsider should NOT be able to delete other team server'
        );
    }

    /** @test */
    public function user_without_team_cannot_view_server()
    {
        $userWithoutTeam = User::factory()->create();

        $this->assertFalse(
            $this->policy->view($userWithoutTeam, $this->server),
            'User without team should NOT be able to view any server'
        );
    }

    /** @test */
    public function team_viewer_cannot_delete_server()
    {
        $viewer = User::factory()->create();
        $this->team->users()->attach($viewer->id, ['role' => 'viewer']);
        $viewer->update(['current_team_id' => $this->team->id]);

        $this->assertFalse(
            $this->policy->delete($viewer, $this->server),
            'Team viewer should NOT have delete-resources permission'
        );
    }

    /** @test */
    public function only_team_owner_can_force_delete_server()
    {
        $this->assertTrue(
            $this->policy->forceDelete($this->owner, $this->server),
            'Team owner should be able to force delete server'
        );

        $this->assertFalse(
            $this->policy->forceDelete($this->teamMember, $this->server),
            'Team member should NOT be able to force delete server'
        );
    }
}
