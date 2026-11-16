<?php

namespace Database\Factories;

use App\Modules\Identity\Models\SocialIdentity;
use App\Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialIdentity>
 */
class SocialIdentityFactory extends Factory
{
    protected $model = SocialIdentity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => SocialIdentity::PROVIDER_GOOGLE,
            'provider_user_id' => (string) fake()->unique()->numerify('##########'),
            'linked_at' => now(),
        ];
    }
}
