<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true) . ' Server',
            'ip_address' => fake()->ipv4(),
            'ssh_port' => 22,
            'ssh_user' => fake()->randomElement(['root', 'ubuntu', 'deploy']),
            'auth_type' => 'key',
            'ssh_key' => null,
            'ssh_password' => null,
            'os_type' => fake()->randomElement(['Ubuntu', 'Debian', 'CentOS']),
            'os_version' => fake()->randomElement(['22.04', '24.04', '20.04']),
            'status' => fake()->randomElement(['online', 'offline', 'provisioning']),
            'last_ping_at' => now()->subMinutes(rand(1, 60)),
            'metadata' => null,
        ];
    }
}
