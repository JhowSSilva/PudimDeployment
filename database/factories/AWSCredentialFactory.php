<?php

namespace Database\Factories;

use App\Models\AWSCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

class AWSCredentialFactory extends Factory
{
    protected $model = AWSCredential::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'access_key_id' => 'AKIA' . $this->faker->bothify('########'),
            'secret_access_key' => $this->faker->lexify(str_repeat('?', 40)),
            'default_region' => 'us-east-1',
            'is_active' => true,
            'team_id' => null,
        ];
    }
}
