<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\Site;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_multi_tenancy_isolation_flow()
    {
        // Setup Team A
        $ownerA = User::factory()->create(['email' => 'owner-a@example.com']);
        $teamA = Team::create([
            'user_id' => $ownerA->id,
            'name' => 'Team A',
            'personal_team' => false,
        ]);
        $teamA->users()->attach($ownerA->id, ['role' => 'admin']);
        $ownerA->update(['current_team_id' => $teamA->id]);

        $memberA = User::factory()->create(['email' => 'member-a@example.com']);
        $teamA->users()->attach($memberA->id, ['role' => 'member']);
        $memberA->update(['current_team_id' => $teamA->id]);

        // Setup Team B
        $ownerB = User::factory()->create(['email' => 'owner-b@example.com']);
        $teamB = Team::create([
            'user_id' => $ownerB->id,
            'name' => 'Team B',
            'personal_team' => false,
        ]);
        $teamB->users()->attach($ownerB->id, ['role' => 'admin']);
        $ownerB->update(['current_team_id' => $teamB->id]);

        // Create servers for each team
        $serverA = Server::create([
            'team_id' => $teamA->id,
            'user_id' => $ownerA->id,
            'name' => 'Server A',
            'ip_address' => '192.168.1.1',
            'ssh_port' => 22,
            'ssh_user' => 'root',
            'status' => 'provisioning',
        ]);

        $serverB = Server::create([
            'team_id' => $teamB->id,
            'user_id' => $ownerB->id,
            'name' => 'Server B',
            'ip_address' => '192.168.1.2',
            'ssh_port' => 22,
            'ssh_user' => 'root',
            'status' => 'provisioning',
        ]);

        // Test 1: Owner A can view their server
        $this->assertTrue(
            $ownerA->can('view', $serverA),
            'Owner A should view Server A'
        );

        // Test 2: Owner A cannot view Team B's server
        $this->assertFalse(
            $ownerA->can('view', $serverB),
            'Owner A should NOT view Server B'
        );

        // Test 3: Member A can view Team A's server
        $this->assertTrue(
            $memberA->can('view', $serverA),
            'Member A should view Server A'
        );

        // Test 4: Member A cannot view Team B's server
        $this->assertFalse(
            $memberA->can('view', $serverB),
            'Member A should NOT view Server B'
        );

        // Test 5: Owner B can only see their team's server
        $this->assertTrue(
            $ownerB->can('view', $serverB),
            'Owner B should view Server B'
        );

        $this->assertFalse(
            $ownerB->can('view', $serverA),
            'Owner B should NOT view Server A'
        );

        // Test 6: Database queries respect team_id
        $this->assertEquals(1, Server::where('team_id', $teamA->id)->count());
        $this->assertEquals(1, Server::where('team_id', $teamB->id)->count());
        
        $this->assertEquals('Server A', Server::where('team_id', $teamA->id)->first()->name);
        $this->assertEquals('Server B', Server::where('team_id', $teamB->id)->first()->name);
    }

    /** @test */
    public function team_roles_permissions_work_correctly()
    {
        $owner = User::factory()->create();
        $team = Team::create([
            'user_id' => $owner->id,
            'name' => 'Test Team',
            'personal_team' => false,
        ]);
        $team->users()->attach($owner->id, ['role' => 'admin']);
        $owner->update(['current_team_id' => $team->id]);

        // Create different role users
        $admin = User::factory()->create();
        $team->users()->attach($admin->id, ['role' => 'admin']);
        $admin->update(['current_team_id' => $team->id]);

        $manager = User::factory()->create();
        $team->users()->attach($manager->id, ['role' => 'manager']);
        $manager->update(['current_team_id' => $team->id]);

        $member = User::factory()->create();
        $team->users()->attach($member->id, ['role' => 'member']);
        $member->update(['current_team_id' => $team->id]);

        $viewer = User::factory()->create();
        $team->users()->attach($viewer->id, ['role' => 'viewer']);
        $viewer->update(['current_team_id' => $team->id]);

        $server = Server::create([
            'team_id' => $team->id,
            'user_id' => $owner->id,
            'name' => 'Test Server',
            'ip_address' => '10.0.0.1',
            'ssh_port' => 22,
            'ssh_user' => 'root',
            'status' => 'provisioning',
        ]);

        // All should be able to view
        $this->assertTrue($owner->can('view', $server));
        $this->assertTrue($admin->can('view', $server));
        $this->assertTrue($manager->can('view', $server));
        $this->assertTrue($member->can('view', $server));
        $this->assertTrue($viewer->can('view', $server));

        // Admin, manager, member can update
        $this->assertTrue($admin->can('update', $server));
        $this->assertTrue($manager->can('update', $server));
        $this->assertTrue($member->can('update', $server));
        
        // Viewer cannot update
        $this->assertFalse($viewer->can('update', $server));

        // Only admin and manager can delete
        $this->assertTrue($admin->can('delete', $server));
        $this->assertTrue($manager->can('delete', $server));
        $this->assertFalse($member->can('delete', $server));
        $this->assertFalse($viewer->can('delete', $server));

        // Only owner can force delete
        $this->assertTrue($owner->can('forceDelete', $server));
        $this->assertFalse($admin->can('forceDelete', $server));
        $this->assertFalse($manager->can('forceDelete', $server));
    }

    /** @test */
    public function user_switching_teams_changes_access()
    {
        $user = User::factory()->create();
        
        $teamA = Team::create([
            'user_id' => $user->id,
            'name' => 'Team A',
            'personal_team' => false,
        ]);
        $teamA->users()->attach($user->id, ['role' => 'admin']);

        $teamB = Team::create([
            'user_id' => $user->id,
            'name' => 'Team B',
            'personal_team' => false,
        ]);
        $teamB->users()->attach($user->id, ['role' => 'admin']);

        $serverA = Server::create([
            'team_id' => $teamA->id,
            'user_id' => $user->id,
            'name' => 'Server A',
            'ip_address' => '10.0.0.1',
            'ssh_port' => 22,
            'ssh_user' => 'root',
            'status' => 'provisioning',
        ]);

        $serverB = Server::create([
            'team_id' => $teamB->id,
            'user_id' => $user->id,
            'name' => 'Server B',
            'ip_address' => '10.0.0.2',
            'ssh_port' => 22,
            'ssh_user' => 'root',
            'status' => 'provisioning',
        ]);

        // User on Team A
        $user->update(['current_team_id' => $teamA->id]);
        $user->refresh();
        
        $this->assertTrue($user->can('view', $serverA));
        $this->assertFalse($user->can('view', $serverB));

        // User switches to Team B
        $user->update(['current_team_id' => $teamB->id]);
        $user->refresh();
        
        $this->assertFalse($user->can('view', $serverA));
        $this->assertTrue($user->can('view', $serverB));
    }
}
