<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deployment>
 */
class DeploymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'running', 'success', 'failed']);
        $startedAt = $status !== 'pending' ? now()->subMinutes(rand(1, 30)) : null;
        $finishedAt = in_array($status, ['success', 'failed']) ? now() : null;
        
        return [
            'site_id' => Site::factory(),
            'user_id' => User::factory(),
            'commit_hash' => fake()->sha1(),
            'commit_message' => fake()->sentence(),
            'status' => $status,
            'trigger' => fake()->randomElement(['manual', 'webhook', 'scheduled']),
            'output_log' => $status !== 'pending' ? fake()->paragraphs(3, true) : null,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'duration_seconds' => $finishedAt ? rand(10, 300) : null,
        ];
    }
}
