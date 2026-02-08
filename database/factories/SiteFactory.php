<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domain = fake()->domainName();
        
        return [
            'server_id' => Server::factory(),
            'name' => fake()->words(2, true),
            'domain' => $domain,
            'document_root' => '/public',
            'php_version' => fake()->randomElement(['8.1', '8.2', '8.3']),
            'git_repository' => 'https://github.com/' . fake()->userName() . '/' . fake()->slug(2),
            'git_branch' => fake()->randomElement(['main', 'master', 'develop']),
            'git_token' => null,
            'deploy_script' => null,
            'env_variables' => null,
            'nginx_config_path' => '/etc/nginx/sites-available/' . $domain,
            'is_active' => true,
            'status' => fake()->randomElement(['active', 'inactive', 'deploying']),
        ];
    }
}
