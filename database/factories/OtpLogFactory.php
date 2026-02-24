<?php

namespace Database\Factories;

use App\Models\OtpLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class OtpLogFactory extends Factory
{
    protected $model = OtpLog::class;

    public function definition(): array
    {
        return [
            'phone' => $this->faker->numerify('+91##########'),
            'otp' => $this->faker->numerify('######'),
            'is_verified' => false,
            'expires_at' => now()->addMinutes(5),
        ];
    }

    /**
     * Mark OTP as verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Mark OTP as expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(10),
        ]);
    }
}
