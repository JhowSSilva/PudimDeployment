<?php

namespace Tests\Feature;

use App\Jobs\InstallServerStackJob;
use App\Models\Server;
use App\Models\User;
use App\Models\Team;
use App\Services\SSH\SSHService;
use App\Services\StackInstallationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InstallServerStackJobTest extends TestCase
{
    use RefreshDatabase;

    protected Server $server;
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user and team
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create();
        $this->user->teams()->attach($this->team);
        
        // Create test server
        $this->server = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'php',
            'language_version' => '8.2',
            'webserver' => 'nginx',
            'database' => 'mysql',
            'cache' => 'redis',
            'status' => 'provisioning'
        ]);
    }

    public function test_job_can_be_dispatched()
    {
        Queue::fake();
        
        InstallServerStackJob::dispatch($this->server);
        
        Queue::assertPushed(InstallServerStackJob::class, function ($job) {
            return $job->server->id === $this->server->id;
        });
    }

    public function test_job_updates_server_status_on_success()
    {
        // Mock the dependencies
        $mockSSHService = $this->createMock(SSHService::class);
        $mockConnection = $this->createMock(\phpseclib3\Net\SSH2::class);
        
        $mockSSHService->method('connect')->willReturn($mockConnection);
        $mockConnection->method('exec')->willReturn('test output');
        $mockConnection->method('isConnected')->willReturn(true);
        
        $mockStackService = $this->createMock(StackInstallationService::class);
        $mockStackService->method('install')->willReturn([
            'status' => 'success',
            'message' => 'Installation completed successfully'
        ]);
        
        $this->app->instance(SSHService::class, $mockSSHService);
        $this->app->instance(StackInstallationService::class, $mockStackService);
        
        $job = new InstallServerStackJob($this->server);
        $job->handle();
        
        $this->server->refresh();
        $this->assertEquals('active', $this->server->status);
    }

    public function test_job_updates_server_status_on_failure()
    {
        // Mock the dependencies to throw an exception
        $mockSSHService = $this->createMock(SSHService::class);
        $mockSSHService->method('connect')->willThrowException(new \Exception('Connection failed'));
        
        $this->app->instance(SSHService::class, $mockSSHService);
        
        $job = new InstallServerStackJob($this->server);
        
        $this->expectException(\Exception::class);
        $job->handle();
        
        $this->server->refresh();
        $this->assertEquals('failed', $this->server->status);
    }

    public function test_job_handles_php_installation()
    {
        $phpServer = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'php',
            'language_version' => '8.2',
            'status' => 'provisioning'
        ]);
        
        // Mock successful PHP installation
        $mockSSHService = $this->createMock(SSHService::class);
        $mockConnection = $this->createMock(\phpseclib3\Net\SSH2::class);
        
        $mockSSHService->method('connect')->willReturn($mockConnection);
        $mockConnection->method('exec')->willReturn('PHP 8.2.0');
        $mockConnection->method('isConnected')->willReturn(true);
        
        $mockStackService = $this->createMock(StackInstallationService::class);
        $mockStackService->method('install')->willReturn([
            'status' => 'success',
            'message' => 'PHP stack installed successfully'
        ]);
        
        $this->app->instance(SSHService::class, $mockSSHService);
        $this->app->instance(StackInstallationService::class, $mockStackService);
        
        $job = new InstallServerStackJob($phpServer);
        $job->handle();
        
        $phpServer->refresh();
        $this->assertEquals('active', $phpServer->status);
    }

    public function test_job_handles_nodejs_installation()
    {
        $nodeServer = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'nodejs',
            'language_version' => '18',
            'status' => 'provisioning'
        ]);
        
        // Mock successful Node.js installation
        $mockSSHService = $this->createMock(SSHService::class);
        $mockConnection = $this->createMock(\phpseclib3\Net\SSH2::class);
        
        $mockSSHService->method('connect')->willReturn($mockConnection);
        $mockConnection->method('exec')->willReturn('v18.0.0');
        $mockConnection->method('isConnected')->willReturn(true);
        
        $mockStackService = $this->createMock(StackInstallationService::class);
        $mockStackService->method('install')->willReturn([
            'status' => 'success',
            'message' => 'Node.js stack installed successfully'
        ]);
        
        $this->app->instance(SSHService::class, $mockSSHService);
        $this->app->instance(StackInstallationService::class, $mockStackService);
        
        $job = new InstallServerStackJob($nodeServer);
        $job->handle();
        
        $nodeServer->refresh();
        $this->assertEquals('active', $nodeServer->status);
    }

    public function test_job_handles_python_installation()
    {
        $pythonServer = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'python',
            'language_version' => '3.11',
            'status' => 'provisioning'
        ]);
        
        // Mock successful Python installation
        $mockSSHService = $this->createMock(SSHService::class);
        $mockConnection = $this->createMock(\phpseclib3\Net\SSH2::class);
        
        $mockSSHService->method('connect')->willReturn($mockConnection);
        $mockConnection->method('exec')->willReturn('Python 3.11.0');
        $mockConnection->method('isConnected')->willReturn(true);
        
        $mockStackService = $this->createMock(StackInstallationService::class);
        $mockStackService->method('install')->willReturn([
            'status' => 'success',
            'message' => 'Python stack installed successfully'
        ]);
        
        $this->app->instance(SSHService::class, $mockSSHService);
        $this->app->instance(StackInstallationService::class, $mockStackService);
        
        $job = new InstallServerStackJob($pythonServer);
        $job->handle();
        
        $pythonServer->refresh();
        $this->assertEquals('active', $pythonServer->status);
    }

    public function test_job_creates_provision_logs()
    {
        // Mock the dependencies
        $mockSSHService = $this->createMock(SSHService::class);
        $mockConnection = $this->createMock(\phpseclib3\Net\SSH2::class);
        
        $mockSSHService->method('connect')->willReturn($mockConnection);
        $mockConnection->method('exec')->willReturn('test output');
        $mockConnection->method('isConnected')->willReturn(true);
        
        $mockStackService = $this->createMock(StackInstallationService::class);
        $mockStackService->method('install')->willReturn([
            'status' => 'success',
            'message' => 'Installation completed successfully'
        ]);
        
        $this->app->instance(SSHService::class, $mockSSHService);
        $this->app->instance(StackInstallationService::class, $mockStackService);
        
        $initialLogCount = $this->server->provisionLogs()->count();
        
        $job = new InstallServerStackJob($this->server);
        $job->handle();
        
        // Should have created provision logs
        $this->assertGreaterThan($initialLogCount, $this->server->provisionLogs()->count());
    }

    public function test_job_timeout_configuration()
    {
        $job = new InstallServerStackJob($this->server);
        
        // Job should have proper timeout configured (3 hours)
        $this->assertEquals(10800, $job->timeout);
    }

    public function test_job_retry_configuration()
    {
        $job = new InstallServerStackJob($this->server);
        
        // Job should be configured to retry on failure
        $this->assertEquals(2, $job->tries);
    }

    public function test_job_failed_method_updates_server_status()
    {
        $job = new InstallServerStackJob($this->server);
        $exception = new \Exception('Test failure');
        
        $job->failed($exception);
        
        $this->server->refresh();
        $this->assertEquals('failed', $this->server->status);
    }
}
