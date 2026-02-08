<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Team;
use App\Traits\StructuredLogging;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class StructuredLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected $testClass;

    protected function setUp(): void
    {
        parent::setUp();

        // Create anonymous class using the trait
        $this->testClass = new class {
            use StructuredLogging;

            public function testLogInfo($message, $context = [])
            {
                $this->logInfo($message, $context);
            }

            public function testLogError($message, $context = [], $exception = null)
            {
                $this->logError($message, $context, $exception);
            }

            public function testEnrichContext($context = [])
            {
                return $this->enrichContext($context);
            }
        };
    }

    /** @test */
    public function enriches_context_with_timestamp()
    {
        $enriched = $this->testClass->testEnrichContext([]);

        $this->assertArrayHasKey('timestamp', $enriched);
        $this->assertNotEmpty($enriched['timestamp']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
            $enriched['timestamp']
        );
    }

    /** @test */
    public function enriches_context_with_environment()
    {
        $enriched = $this->testClass->testEnrichContext([]);

        $this->assertArrayHasKey('environment', $enriched);
        $this->assertEquals(config('app.env'), $enriched['environment']);
    }

    /** @test */
    public function enriches_context_with_user_info_when_authenticated()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->actingAs($user);

        $enriched = $this->testClass->testEnrichContext([]);

        $this->assertArrayHasKey('user', $enriched);
        $this->assertEquals($user->id, $enriched['user']['id']);
        $this->assertEquals('test@example.com', $enriched['user']['email']);
    }

    /** @test */
    public function enriches_context_with_team_info_when_user_has_team()
    {
        $user = User::factory()->create();
        $team = Team::create([
            'user_id' => $user->id,
            'name' => 'Test Team',
            'personal_team' => false,
        ]);
        $team->users()->attach($user->id, ['role' => 'admin']);
        $user->update(['current_team_id' => $team->id]);

        $this->actingAs($user);

        $enriched = $this->testClass->testEnrichContext([]);

        $this->assertArrayHasKey('team', $enriched);
        $this->assertEquals($team->id, $enriched['team']['id']);
        $this->assertEquals('Test Team', $enriched['team']['name']);
    }

    /** @test */
    public function does_not_include_user_context_when_not_authenticated()
    {
        $enriched = $this->testClass->testEnrichContext([]);

        $this->assertArrayNotHasKey('user', $enriched);
        $this->assertArrayNotHasKey('team', $enriched);
    }

    /** @test */
    public function merges_custom_context_with_enriched_context()
    {
        $customContext = [
            'deployment_id' => 123,
            'site_id' => 456,
            'custom_field' => 'custom_value',
        ];

        $enriched = $this->testClass->testEnrichContext($customContext);

        // Should have both enriched and custom fields
        $this->assertArrayHasKey('timestamp', $enriched);
        $this->assertArrayHasKey('environment', $enriched);
        $this->assertArrayHasKey('deployment_id', $enriched);
        $this->assertArrayHasKey('site_id', $enriched);
        $this->assertArrayHasKey('custom_field', $enriched);
        
        $this->assertEquals(123, $enriched['deployment_id']);
        $this->assertEquals(456, $enriched['site_id']);
        $this->assertEquals('custom_value', $enriched['custom_field']);
    }

    /** @test */
    public function log_error_includes_exception_details()
    {
        Log::shouldReceive('error')
            ->once()
            ->with(
                'Test error message',
                \Mockery::on(function ($context) {
                    return isset($context['exception']) &&
                           isset($context['exception']['class']) &&
                           isset($context['exception']['message']) &&
                           isset($context['exception']['file']) &&
                           isset($context['exception']['line']) &&
                           isset($context['exception']['trace']);
                })
            );

        $exception = new \RuntimeException('Test exception');
        
        $this->testClass->testLogError('Test error message', [], $exception);
    }

    /** @test */
    public function preserves_custom_context_when_logging()
    {
        $customContext = [
            'server_id' => 789,
            'action' => 'deploy',
        ];

        Log::shouldReceive('info')
            ->once()
            ->with(
                'Deployment started',
                \Mockery::on(function ($context) use ($customContext) {
                    return $context['server_id'] === 789 &&
                           $context['action'] === 'deploy' &&
                           isset($context['timestamp']) &&
                           isset($context['environment']);
                })
            );

        $this->testClass->testLogInfo('Deployment started', $customContext);
    }

    /** @test */
    public function custom_context_can_override_enriched_fields()
    {
        // Custom context should take precedence
        $customContext = [
            'environment' => 'custom-env',
        ];

        $enriched = $this->testClass->testEnrichContext($customContext);

        // Custom value should be preserved
        $this->assertEquals('custom-env', $enriched['environment']);
    }
}
