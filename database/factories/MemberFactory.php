<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Temple;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'temple_id' => Temple::factory(),
            'role_id' => null,
            'name' => $this->faker->name(),
            'phone' => $this->faker->unique()->numerify('+91##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => 'member',
        ];
    }
}
