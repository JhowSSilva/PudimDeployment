<?php

namespace Tests\Unit\Services;

use App\Models\Server;
use App\Services\SSHService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SSHServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SSHService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SSHService();
    }

    public function test_can_generate_ssh_key_pair(): void
    {
        $keyPair = $this->service->generateKeyPair();

        $this->assertIsArray($keyPair);
        $this->assertArrayHasKey('private', $keyPair);
        $this->assertArrayHasKey('public', $keyPair);
        $this->assertStringContainsString('BEGIN PRIVATE KEY', $keyPair['private']);
        $this->assertStringContainsString('ssh-rsa', $keyPair['public']);
    }

    public function test_generated_keys_are_different_each_time(): void
    {
        $keyPair1 = $this->service->generateKeyPair();
        $keyPair2 = $this->service->generateKeyPair();

        $this->assertNotEquals($keyPair1['private'], $keyPair2['private']);
        $this->assertNotEquals($keyPair1['public'], $keyPair2['public']);
    }

    public function test_generated_public_key_contains_app_comment(): void
    {
        $keyPair = $this->service->generateKeyPair();
        
        $this->assertStringContainsString(config('app.name'), $keyPair['public']);
    }

    // Note: Connection tests would require actual SSH server or mocking
    // Below is a structure example for when SSH mocking is available
    
    /** 
     * @test
     * This test is skipped by default as it requires actual SSH server
     * Uncomment and configure when SSH mocking is implemented
     */
    public function skip_test_connection_with_invalid_credentials_fails(): void
    {
        $this->markTestSkipped('Requires SSH server or mocking library');
        
        // $server = Server::factory()->create([
        //     'ssh_key_private' => encrypt('invalid-key'),
        // ]);
        // 
        // $this->assertFalse($this->service->testConnection($server));
    }

    /**
     * @test
     * Example structure for command execution test
     */
    public function skip_test_execute_command_returns_expected_format(): void
    {
        $this->markTestSkipped('Requires SSH server or mocking library');
        
        // Expected format when implemented:
        // $result = $this->service->executeCommand($server, 'echo test');
        // $this->assertArrayHasKey('output', $result);
        // $this->assertArrayHasKey('exit_code', $result);
        // $this->assertArrayHasKey('success', $result);
    }
}
