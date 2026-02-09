<?php

namespace Tests\Feature;

use App\Models\InstanceRegistrationToken;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstanceRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_generate_registration_token()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $resp = $this->postJson('/servers/registration-tokens', ['deploy_user' => 'deploy']);
        $resp->assertStatus(200);
        $resp->assertJsonStructure(['token', 'command', 'expires_at']);
    }

    public function test_register_api_accepts_token_and_creates_server()
    {
        $user = User::factory()->create();
        $token = InstanceRegistrationToken::generateForUser($user, null, 60);

        $payload = [
            'ip_address' => '127.0.0.2',
            'ssh_public_key' => 'ssh-ed25519 AAAAB3NzaC1lZDI1NTE5AAAAIMOCKEY',
            'deploy_user' => 'deploy',
        ];

        $resp = $this->postJson('/api/instances/register', $payload, ['X-Registration-Token' => $token->token]);
        $resp->assertStatus(201);

        $this->assertDatabaseHas('servers', ['ip_address' => '127.0.0.2', 'deploy_user' => 'deploy']);
    }

    public function test_token_meta_prefills_server_when_no_payload_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // generate token with meta (simulate user filling form and generating command before creating server)
        $resp = $this->postJson('/servers/registration-tokens', [
            'deploy_user' => 'deploy',
            'name' => 'pre-filled-server',
            'ip_address' => '10.11.12.13',
            'ssh_port' => 2222,
            'os' => 'ubuntu-22.04',
            'type' => 'server',
        ]);

        $resp->assertStatus(200);
        $token = $resp->json('token');

        // now call register endpoint with only ssh_public_key
        $payload = [
            'ssh_public_key' => 'ssh-ed25519 AAAAB3NzaC1lZDI1NTE5AAAAIMOCKEY',
        ];

        $reg = $this->postJson('/api/instances/register', $payload, ['X-Registration-Token' => $token]);
        $reg->assertStatus(201);

        $this->assertDatabaseHas('servers', ['name' => 'pre-filled-server', 'ip_address' => '10.11.12.13', 'ssh_port' => 2222]);
    }
}
