<?php

namespace Database\Factories;

use App\Models\DigitalOceanCredential;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalOceanCredential>
 */
class DigitalOceanCredentialFactory extends Factory
{
    protected $model = DigitalOceanCredential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->company() . ' DigitalOcean Token',
            'api_token' => 'dop_v1_' . fake()->sha256(),
            'region' => fake()->randomElement(['nyc1', 'nyc3', 'sfo3', 'ams3', 'sgp1']),
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