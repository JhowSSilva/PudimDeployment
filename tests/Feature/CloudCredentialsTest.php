<?php

namespace Tests\Feature;

use App\Models\AzureCredential;
use App\Models\GcpCredential;
use App\Models\DigitalOceanCredential;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CloudCredentialsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip cloud validation in tests
        config(['app.skip_cloud_validation' => true]);
        
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create();
        $this->user->teams()->attach($this->team, ['role' => 'admin']);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    #[Test]
    public function user_can_view_azure_credentials_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('azure-credentials.index'));

        $response->assertOk();
        $response->assertViewIs('azure-credentials.index');
    }

    #[Test]
    public function user_can_create_azure_credential()
    {
        $credentialData = [
            'name' => 'Test Azure',
            'subscription_id' => '12345678-1234-1234-1234-123456789abc',
            'tenant_id' => '87654321-4321-4321-4321-cba987654321',
            'client_id' => '11111111-1111-1111-1111-111111111111',
            'client_secret' => 'test-secret',
            'region' => 'eastus',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('azure-credentials.store'), $credentialData);

        $response->assertRedirect(route('azure-credentials.index'));
        $this->assertDatabaseHas('azure_credentials', [
            'name' => 'Test Azure',
            'subscription_id' => '12345678-1234-1234-1234-123456789abc',
            'team_id' => $this->team->id,
        ]);
    }

    #[Test]
    public function user_can_view_gcp_credentials_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('gcp-credentials.index'));

        $response->assertOk();
        $response->assertViewIs('gcp-credentials.index');
    }

    #[Test]
    public function user_can_create_gcp_credential()
    {
        $credentialData = [
            'name' => 'Test GCP',
            'project_id' => 'test-project',
            'service_account_json' => '{"type": "service_account", "project_id": "test"}',
            'region' => 'us-central1',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('gcp-credentials.store'), $credentialData);

        $response->assertRedirect(route('gcp-credentials.index'));
        $this->assertDatabaseHas('gcp_credentials', [
            'name' => 'Test GCP',
            'project_id' => 'test-project',
            'team_id' => $this->team->id,
        ]);
    }

    #[Test]
    public function user_can_view_digitalocean_credentials_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('digitalocean-credentials.index'));

        $response->assertOk();
        $response->assertViewIs('digitalocean-credentials.index');
    }

    #[Test]
    public function user_can_create_digitalocean_credential()
    {
        $credentialData = [
            'name' => 'Test DigitalOcean',
            'api_token' => 'dop_v1_test_token_123',
            'region' => 'nyc3',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('digitalocean-credentials.store'), $credentialData);

        $response->assertRedirect(route('digitalocean-credentials.index'));
        $this->assertDatabaseHas('digitalocean_credentials', [
            'name' => 'Test DigitalOcean',
            'team_id' => $this->team->id,
        ]);
    }

    #[Test]
    public function credential_creation_requires_validation()
    {
        // Test Azure validation
        $response = $this->actingAs($this->user)
            ->post(route('azure-credentials.store'), []);

        $response->assertSessionHasErrors(['name', 'subscription_id', 'tenant_id', 'client_id', 'client_secret']);

        // Test GCP validation
        $response = $this->actingAs($this->user)
            ->post(route('gcp-credentials.store'), []);

        $response->assertSessionHasErrors(['name', 'project_id', 'service_account_json']);

        // Test DigitalOcean validation
        $response = $this->actingAs($this->user)
            ->post(route('digitalocean-credentials.store'), []);

        $response->assertSessionHasErrors(['name', 'api_token']);
    }

    #[Test]
    public function credentials_are_encrypted_in_database()
    {
        $azure = AzureCredential::factory()->create([
            'team_id' => $this->team->id,
            'client_secret' => 'test-secret'
        ]);

        $gcp = GcpCredential::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $do = DigitalOceanCredential::factory()->create([
            'team_id' => $this->team->id,
            'api_token' => 'test-token'
        ]);

        // Check that sensitive data is encrypted in database
        $this->assertDatabaseMissing('azure_credentials', [
            'id' => $azure->id,
            'client_secret' => 'test-secret'
        ]);

        $this->assertDatabaseMissing('digitalocean_credentials', [
            'id' => $do->id,
            'api_token' => 'test-token'
        ]);
    }

    #[Test]
    public function first_credential_is_set_as_default()
    {
        // Test Azure
        $azure = AzureCredential::factory()->create(['team_id' => $this->team->id]);
        $this->assertTrue($azure->fresh()->is_default);

        // Test GCP
        $gcp = GcpCredential::factory()->create(['team_id' => $this->team->id]);
        $this->assertTrue($gcp->fresh()->is_default);

        // Test DigitalOcean
        $do = DigitalOceanCredential::factory()->create(['team_id' => $this->team->id]);
        $this->assertTrue($do->fresh()->is_default);
    }
}