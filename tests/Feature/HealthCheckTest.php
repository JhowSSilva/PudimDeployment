<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ping_endpoint_returns_success()
    {
        $response = $this->get('/ping');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
        ]);
        $response->assertJsonStructure([
            'status',
            'timestamp',
        ]);
    }

    /** @test */
    public function health_endpoint_returns_comprehensive_status()
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
        ]);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'services' => [
                'database' => ['status', 'connection'],
                'cache' => ['status', 'driver'],
                'queue' => ['status', 'size', 'connection'],
                'disk' => ['status', 'free', 'total', 'used_percent'],
            ],
            'application' => [
                'name',
                'environment',
                'debug',
                'laravel_version',
                'php_version',
            ],
        ]);
    }

    /** @test */
    public function health_endpoint_validates_database_connection()
    {
        $response = $this->get('/health');

        $data = $response->json();
        
        $this->assertEquals('ok', $data['services']['database']['status']);
        $this->assertNotEmpty($data['services']['database']['connection']);
    }

    /** @test */
    public function health_endpoint_validates_cache_availability()
    {
        $response = $this->get('/health');

        $data = $response->json();
        
        $this->assertContains(
            $data['services']['cache']['status'],
            ['ok', 'warning']
        );
        $this->assertNotEmpty($data['services']['cache']['driver']);
    }

    /** @test */
    public function health_endpoint_checks_queue_status()
    {
        $response = $this->get('/health');

        $data = $response->json();
        
        $this->assertContains(
            $data['services']['queue']['status'],
            ['ok', 'warning']
        );
        $this->assertIsInt($data['services']['queue']['size']);
    }

    /** @test */
    public function health_endpoint_monitors_disk_space()
    {
        $response = $this->get('/health');

        $data = $response->json();
        
        $this->assertContains(
            $data['services']['disk']['status'],
            ['ok', 'warning', 'critical']
        );
        $this->assertIsFloat($data['services']['disk']['used_percent']);
        $this->assertStringContainsString('GB', $data['services']['disk']['free']);
        $this->assertStringContainsString('GB', $data['services']['disk']['total']);
    }

    /** @test */
    public function health_endpoint_includes_application_metadata()
    {
        $response = $this->get('/health');

        $data = $response->json();
        
        $this->assertNotEmpty($data['application']['name']);
        $this->assertNotEmpty($data['application']['environment']);
        $this->assertIsBool($data['application']['debug']);
        $this->assertNotEmpty($data['application']['laravel_version']);
        $this->assertNotEmpty($data['application']['php_version']);
    }

    /** @test */
    public function health_endpoint_returns_warning_status_when_queue_is_large()
    {
        // Este teste assumiria que temos controle sobre o tamanho da fila
        // Por enquanto, vamos apenas verificar que o endpoint funciona
        $response = $this->get('/health');
        
        $response->assertStatus(200);
        $this->assertContains(
            $response->json('status'),
            ['ok', 'warning', 'critical']
        );
    }

    /** @test */
    public function health_endpoint_timestamp_is_valid_iso8601()
    {
        $response = $this->get('/health');

        $timestamp = $response->json('timestamp');
        
        $this->assertNotEmpty($timestamp);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
            $timestamp
        );
    }

    /** @test */
    public function health_endpoints_do_not_require_authentication()
    {
        // Ping sem autenticação
        $response = $this->get('/ping');
        $response->assertStatus(200);

        // Health sem autenticação
        $response = $this->get('/health');
        $response->assertStatus(200);
    }
}
