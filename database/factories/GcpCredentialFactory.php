<?php

namespace Database\Factories;

use App\Models\GcpCredential;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GcpCredential>
 */
class GcpCredentialFactory extends Factory
{
    protected $model = GcpCredential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $projectId = fake()->slug() . '-' . fake()->randomNumber(6);
        
        return [
            'team_id' => Team::factory(),
            'name' => fake()->company() . ' GCP Project',
            'project_id' => $projectId,
            'service_account_json' => json_encode([
                'type' => 'service_account',
                'project_id' => $projectId,
                'private_key_id' => fake()->sha256(),
                'private_key' => '-----BEGIN PRIVATE KEY-----\n' . fake()->text(1000) . '\n-----END PRIVATE KEY-----\n',
                'client_email' => 'service-account@' . $projectId . '.iam.gserviceaccount.com',
                'client_id' => fake()->randomNumber(9), // Reduced from 21 to 9
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
            ]),
            'region' => fake()->randomElement(['us-central1', 'us-east1', 'europe-west1', 'asia-southeast1']),
            'is_default' => false,
        ];
    }

    /**
     * Indicate that the credential is the default one.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}