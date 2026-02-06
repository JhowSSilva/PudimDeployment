<?php

namespace Database\Factories;

use App\Models\AzureCredential;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AzureCredential>
 */
class AzureCredentialFactory extends Factory
{
    protected $model = AzureCredential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->company() . ' Azure Account',
            'subscription_id' => fake()->uuid(),
            'tenant_id' => fake()->uuid(),
            'client_id' => fake()->uuid(),
            'client_secret' => fake()->password(32),
            'region' => fake()->randomElement(['eastus', 'westus2', 'westeurope', 'southeastasia']),
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