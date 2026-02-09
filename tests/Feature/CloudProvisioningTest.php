<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\User;
use App\Models\AWSCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloudProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_aws_credentials_empty_by_default()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $resp = $this->get('/cloud/aws/credentials');
        $resp->assertStatus(200);
        $this->assertIsArray($resp->json('credentials'));
    }

    public function test_provision_stub_aws_creates_server_record()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a fake credential associated with user's team
        $cred = AWSCredential::factory()->create(['team_id' => $user->currentTeam->id, 'is_active' => true]);

        $payload = [
            'name' => 'provision-test',
            'credential_id' => $cred->id,
            'region' => 'us-east-1',
            'instance_type' => 't3.micro',
            'disk_size' => 20,
            'arch' => 'x86_64',
        ];

        $resp = $this->post('/cloud/aws/provision', $payload);
        $resp->assertStatus(201);
        $this->assertDatabaseHas('servers', ['name' => 'provision-test']);
    }
}
