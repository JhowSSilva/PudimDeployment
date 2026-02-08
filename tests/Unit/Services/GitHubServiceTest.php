<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\GitHubService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GitHubServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_instantiate_service_without_user(): void
    {
        $service = new GitHubService();
        
        $this->assertInstanceOf(GitHubService::class, $service);
        $this->assertNull($service->getClient());
    }

    public function test_can_instantiate_service_with_user_without_github(): void
    {
        $user = User::factory()->create([
            'github_token' => null,
        ]);
        
        $service = new GitHubService($user);
        
        $this->assertInstanceOf(GitHubService::class, $service);
        $this->assertNull($service->getClient());
    }

    public function test_can_authenticate_with_token(): void
    {
        $service = new GitHubService();
        $service->authenticate('fake_token_for_testing');
        
        $this->assertNotNull($service->getClient());
        $this->assertInstanceOf(\Github\Client::class, $service->getClient());
    }

    public function test_rate_limit_check_returns_boolean(): void
    {
        $service = new GitHubService();
        $service->authenticate('fake_token');
        
        // Should return false if rate limit check fails (expected with fake token)
        $result = $service->isApproachingRateLimit();
        
        $this->assertIsBool($result);
    }

    /**
     * @test
     * This test requires valid GitHub token
     * Skipped by default, uncomment when testing with real credentials
     */
    public function skip_test_connection_with_valid_token(): void
    {
        $this->markTestSkipped('Requires valid GitHub token');
        
        // To test with real token:
        // $token = env('GITHUB_TEST_TOKEN');
        // $service = new GitHubService();
        // $service->authenticate($token);
        // $this->assertTrue($service->testConnection());
    }

    /**
     * @test
     * This test requires valid GitHub token
     */
    public function skip_test_can_get_authenticated_user(): void
    {
        $this->markTestSkipped('Requires valid GitHub token');
        
        // To test with real token:
        // $token = env('GITHUB_TEST_TOKEN');
        // $service = new GitHubService();
        // $service->authenticate($token);
        // $user = $service->getAuthenticatedUser();
        // $this->assertIsArray($user);
        // $this->assertArrayHasKey('login', $user);
    }

    /**
     * @test
     * Test that service handles invalid token gracefully
     */
    public function skip_test_connection_fails_with_invalid_token(): void
    {
        $this->markTestSkipped('Requires GitHub API call');
        
        // $service = new GitHubService();
        // $service->authenticate('invalid_token_12345');
        // $this->assertFalse($service->testConnection());
    }

    public function test_service_can_be_chained_after_authenticate(): void
    {
        $service = new GitHubService();
        
        $result = $service->authenticate('test_token');
        
        $this->assertInstanceOf(GitHubService::class, $result);
        $this->assertSame($service, $result); // Same instance
    }
}
