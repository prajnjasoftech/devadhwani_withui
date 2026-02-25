<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Temple;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'temple_id' => Temple::factory(),
            'role_name' => $this->faker->unique()->jobTitle(),
            'role' => $this->faker->randomElements(
                ['dashboard', 'devotees', 'members', 'poojas', 'bookings', 'categories', 'items'],
                $this->faker->numberBetween(2, 5)
            ),
        ];
    }
}
