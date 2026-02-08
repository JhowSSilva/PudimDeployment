<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\User;
use App\Models\Team;
use App\Services\StackInstallationService;
use App\Services\Installers\PHPStackInstaller;
use App\Services\Installers\NodeStackInstaller;
use App\Services\Installers\PythonStackInstaller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class StackInstallationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StackInstallationService $stackService;
    protected Server $server;
    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->stackService = app(StackInstallationService::class);
        
        // Create test user and team
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create();
        $this->user->teams()->attach($this->team);
        
        // Create test server
        $this->server = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'php',
            'language_version' => '8.2',
            'status' => 'provisioning'
        ]);
    }

    public function test_service_registers_default_installers()
    {
        $installers = $this->stackService->getAvailableInstallers();
        
        $this->assertArrayHasKey('php', $installers);
        $this->assertArrayHasKey('nodejs', $installers);
        $this->assertArrayHasKey('python', $installers);
        
        $this->assertInstanceOf(PHPStackInstaller::class, $installers['php']);
        $this->assertInstanceOf(NodeStackInstaller::class, $installers['nodejs']);
        $this->assertInstanceOf(PythonStackInstaller::class, $installers['python']);
    }

    public function test_can_register_custom_installer()
    {
        $customInstaller = new class extends \App\Services\Installers\AbstractStackInstaller {
            public function install($connection, array $config = []): array
            {
                return ['status' => 'success', 'message' => 'Custom installer test'];
            }
            
            public function validate($connection): bool
            {
                return true;
            }
            
            public function getRequiredPackages(): array
            {
                return ['test-package'];
            }
        };
        
        $this->stackService->registerInstaller('custom', $customInstaller);
        
        $installers = $this->stackService->getAvailableInstallers();
        $this->assertArrayHasKey('custom', $installers);
        $this->assertEquals($customInstaller, $installers['custom']);
    }

    public function test_install_throws_exception_for_unsupported_language()
    {
        $server = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'unsupported_language',
            'status' => 'provisioning'
        ]);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No installer available for language: unsupported_language');
        
        $this->stackService->install($server, []);
    }

    public function test_installation_progress_tracking()
    {
        // Mock Redis for progress tracking
        Redis::shouldReceive('set')->once();
        Redis::shouldReceive('get')->andReturn(json_encode([
            'base_system' => ['status' => 'completed', 'timestamp' => now()],
            'programming_stack' => ['status' => 'running', 'timestamp' => now()]
        ]));
        
        $progress = $this->stackService->getInstallationProgress($this->server);
        
        $this->assertIsArray($progress);
        $this->assertArrayHasKey('base_system', $progress);
        $this->assertEquals('completed', $progress['base_system']['status']);
    }

    public function test_installation_time_estimation()
    {
        $estimatedTime = $this->stackService->estimateInstallationTime('php');
        
        $this->assertIsInt($estimatedTime);
        $this->assertGreaterThan(0, $estimatedTime);
    }

    public function test_installation_time_estimation_for_unknown_language()
    {
        $estimatedTime = $this->stackService->estimateInstallationTime('unknown_language');
        
        // Should return default time for unknown languages
        $this->assertEquals(600, $estimatedTime); // 10 minutes default
    }

    public function test_validate_server_configuration_valid()
    {
        $validConfig = [
            'programming_language' => 'php',
            'language_version' => '8.2',
            'webserver' => 'nginx',
            'database' => 'mysql',
            'cache' => 'redis'
        ];
        
        $result = $this->stackService->validateServerConfiguration($validConfig);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validate_server_configuration_invalid()
    {
        $invalidConfig = [
            'programming_language' => '', // Required field missing
            'language_version' => 'invalid_version',
            'webserver' => 'unknown_webserver'
        ];
        
        $result = $this->stackService->validateServerConfiguration($invalidConfig);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_get_supported_languages()
    {
        $languages = $this->stackService->getSupportedLanguages();
        
        $this->assertIsArray($languages);
        $this->assertContains('php', $languages);
        $this->assertContains('nodejs', $languages);
        $this->assertContains('python', $languages);
    }

    public function test_get_available_versions_for_language()
    {
        $phpVersions = $this->stackService->getAvailableVersions('php');
        
        $this->assertIsArray($phpVersions);
        $this->assertNotEmpty($phpVersions);
        $this->assertContains('8.2', $phpVersions);
    }

    public function test_get_available_versions_for_unknown_language()
    {
        $versions = $this->stackService->getAvailableVersions('unknown_language');
        
        $this->assertIsArray($versions);
        $this->assertEmpty($versions);
    }

    public function test_installation_with_php_language()
    {
        $server = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'php',
            'language_version' => '8.2',
            'webserver' => 'nginx',
            'status' => 'provisioning'
        ]);
        
        // Mock the installer behavior
        $mockInstaller = $this->createMock(PHPStackInstaller::class);
        $mockInstaller->method('install')->willReturn([
            'status' => 'success',
            'message' => 'PHP stack installed successfully'
        ]);
        
        $this->stackService->registerInstaller('php', $mockInstaller);
        
        $result = $this->stackService->install($server, []);
        
        $this->assertEquals('success', $result['status']);
        $this->assertStringContains('PHP stack installed successfully', $result['message']);
    }

    public function test_installation_with_nodejs_language()
    {
        $server = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'nodejs',
            'language_version' => '18',
            'webserver' => 'nginx',
            'status' => 'provisioning'
        ]);
        
        // Mock the installer behavior
        $mockInstaller = $this->createMock(NodeStackInstaller::class);
        $mockInstaller->method('install')->willReturn([
            'status' => 'success',
            'message' => 'Node.js stack installed successfully'
        ]);
        
        $this->stackService->registerInstaller('nodejs', $mockInstaller);
        
        $result = $this->stackService->install($server, []);
        
        $this->assertEquals('success', $result['status']);
        $this->assertStringContains('Node.js stack installed successfully', $result['message']);
    }

    public function test_installation_with_python_language()
    {
        $server = Server::factory()->create([
            'team_id' => $this->team->id,
            'programming_language' => 'python',
            'language_version' => '3.11',
            'webserver' => 'nginx',
            'status' => 'provisioning'
        ]);
        
        // Mock the installer behavior
        $mockInstaller = $this->createMock(PythonStackInstaller::class);
        $mockInstaller->method('install')->willReturn([
            'status' => 'success',
            'message' => 'Python stack installed successfully'
        ]);
        
        $this->stackService->registerInstaller('python', $mockInstaller);
        
        $result = $this->stackService->install($server, []);
        
        $this->assertEquals('success', $result['status']);
        $this->assertStringContains('Python stack installed successfully', $result['message']);
    }
}
