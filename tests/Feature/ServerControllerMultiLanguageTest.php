<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\User;
use App\Models\Team;
use App\Services\StackInstallationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ServerControllerMultiLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate user
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create();
        $this->user->teams()->attach($this->team);
        $this->user->current_team_id = $this->team->id;
        $this->user->save();
        
        $this->actingAs($this->user);
    }

    public function test_can_create_server_with_php_language()
    {
        Queue::fake();
        
        $serverData = [
            'name' => 'PHP Test Server',
            'provider' => 'aws',
            'size' => 't3.micro',
            'region' => 'us-east-1',
            'programming_language' => 'php',
            'language_version' => '8.2',
            'webserver' => 'nginx',
            'database' => 'mysql',
            'cache' => 'redis',
            'ssh_username' => 'ubuntu',
            'ssh_password' => 'password123'
        ];
        
        $response = $this->post(route('servers.store'), $serverData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('servers', [
            'name' => 'PHP Test Server',
            'programming_language' => 'php',
            'language_version' => '8.2',
            'team_id' => $this->team->id
        ]);
    }

    public function test_can_create_server_with_nodejs_language()
    {
        Queue::fake();
        
        $serverData = [
            'name' => 'Node.js Test Server',
            'provider' => 'aws',
            'size' => 't3.micro',
            'region' => 'us-east-1',
            'programming_language' => 'nodejs',
            'language_version' => '18',
            'webserver' => 'nginx',
            'database' => 'mysql',
            'cache' => 'redis',
            'ssh_username' => 'ubuntu',
            'ssh_password' => 'password123'
        ];
        
        $response = $this->post(route('servers.store'), $serverData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('servers', [
            'name' => 'Node.js Test Server',
            'programming_language' => 'nodejs',
            'language_version' => '18',
            'team_id' => $this->team->id
        ]);
    }

    public function test_can_create_server_with_python_language()
    {
        Queue::fake();
        
        $serverData = [
            'name' => 'Python Test Server',
            'provider' => 'aws',
            'size' => 't3.micro',
            'region' => 'us-east-1',
            'programming_language' => 'python',
            'language_version' => '3.11',
            'webserver' => 'nginx',
            'database' => 'postgresql',
            'cache' => 'redis',
            'ssh_username' => 'ubuntu',
            'ssh_password' => 'password123'
        ];
        
        $response = $this->post(route('servers.store'), $serverData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('servers', [
            'name' => 'Python Test Server',
            'programming_language' => 'python',
            'language_version' => '3.11',
            'team_id' => $this->team->id
        ]);
    }

    public function test_get_language_versions_endpoint_returns_php_versions()
    {
        $response = $this->get(route('servers.language-versions', 'php'));
        
        $response->assertOk();
        $response->assertJsonStructure(['versions']);
        
        $versions = $response->json('versions');
        $this->assertContains('8.2', $versions);
        $this->assertContains('8.1', $versions);
        $this->assertContains('8.0', $versions);
    }

    public function test_get_language_versions_endpoint_returns_nodejs_versions()
    {
        $response = $this->get(route('servers.language-versions', 'nodejs'));
        
        $response->assertOk();
        $response->assertJsonStructure(['versions']);
        
        $versions = $response->json('versions');
        $this->assertContains('18', $versions);
        $this->assertContains('16', $versions);
        $this->assertContains('14', $versions);
    }

    public function test_get_language_versions_endpoint_returns_python_versions()
    {
        $response = $this->get(route('servers.language-versions', 'python'));
        
        $response->assertOk();
        $response->assertJsonStructure(['versions']);
        
        $versions = $response->json('versions');
        $this->assertContains('3.11', $versions);
        $this->assertContains('3.10', $versions);  
        $this->assertContains('3.9', $versions);
    }

    public function test_create_server_validates_required_fields()
    {
        $response = $this->post(route('servers.store'), []);
        
        $response->assertSessionHasErrors([
            'name',
            'provider',
            'size',
            'region',
            'programming_language',
            'language_version',
            'webserver'
        ]);
    }

    public function test_create_server_validates_programming_language_field()
    {
        $serverData = [
            'name' => 'Test Server',
            'provider' => 'aws',
            'size' => 't3.micro',
            'region' => 'us-east-1',
            'programming_language' => 'invalid_language',
            'language_version' => '1.0',
            'webserver' => 'nginx',
            'ssh_username' => 'ubuntu',
            'ssh_password' => 'password123'
        ];
        
        $response = $this->post(route('servers.store'), $serverData);
        
        $response->assertSessionHasErrors(['programming_language']);
    }

    public function test_multi_language_create_form_loads()
    {
        $response = $this->get(route('servers.create-multi-language'));
        
        $response->assertOk();
        $response->assertViewIs('servers.create-multi-language');
        $response->assertViewHas('languages');
        
        $languages = $response->viewData('languages');
        $this->assertArrayHasKey('php', $languages);
        $this->assertArrayHasKey('nodejs', $languages);
        $this->assertArrayHasKey('python', $languages);
    }

    public function test_server_creation_dispatches_correct_job()
    {
        Queue::fake();
        
        $serverData = [
            'name' => 'Multi Language Server',
            'provider' => 'aws',
            'size' => 't3.micro',
            'region' => 'us-east-1',
            'programming_language' => 'nodejs',
            'language_version' => '18',
            'webserver' => 'nginx',
            'database' => 'mysql',
            'cache' => 'redis',
            'ssh_username' => 'ubuntu',
            'ssh_password' => 'password123'
        ];
        
        $this->post(route('servers.store'), $serverData);
        
        // Should dispatch the new InstallServerStackJob for multi-language support
        Queue::assertPushed(\App\Jobs\InstallServerStackJob::class);
    }

    public function test_server_list_shows_programming_language()
    {
        // Create servers with different languages
        Server::factory()->create([
            'name' => 'PHP Server',
            'team_id' => $this->team->id,
            'programming_language' => 'php',
            'language_version' => '8.2'
        ]);
        
        Server::factory()->create([
            'name' => 'Node Server',
            'team_id' => $this->team->id,
            'programming_language' => 'nodejs',
            'language_version' => '18'
        ]);
        
        $response = $this->get(route('servers.index'));
        
        $response->assertOk();
        $response->assertSee('PHP Server');
        $response->assertSee('Node Server');
        $response->assertSee('PHP');
        $response->assertSee('Node.js');
    }

    public function test_server_detail_shows_language_information()
    {
        $server = Server::factory()->create([
            'name' => 'Test Server Detail',
            'team_id' => $this->team->id,
            'programming_language' => 'python',
            'language_version' => '3.11',
            'webserver' => 'nginx',
            'database' => 'postgresql'
        ]);
        
        $response = $this->get(route('servers.show', $server));
        
        $response->assertOk();
        $response->assertSee('Test Server Detail');
        $response->assertSee('Python');
        $response->assertSee('3.11');
        $response->assertSee('PostgreSQL');
    }

    public function test_server_update_allows_language_version_change()
    {
        $server = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'php',
            'language_version' => '8.1'
        ]);
        
        $updateData = [
            'name' => $server->name,
            'language_version' => '8.2',
            'webserver' => $server->webserver,
            'database' => $server->database
        ];
        
        $response = $this->put(route('servers.update', $server), $updateData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('servers', [
            'id' => $server->id,
            'language_version' => '8.2'
        ]);
    }

    public function test_can_filter_servers_by_programming_language()
    {
        // Create servers with different languages
        Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'php',
            'name' => 'PHP Server 1'
        ]);
        
        Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'nodejs',
            'name' => 'Node Server 1'
        ]);
        
        $response = $this->get(route('servers.index', ['language' => 'php']));
        
        $response->assertOk();
        $response->assertSee('PHP Server 1');
        $response->assertDontSee('Node Server 1');
    }
}
