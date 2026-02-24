<?php

namespace Database\Factories;

use App\Models\Temple;
use Illuminate\Database\Eloquent\Factories\Factory;

class TempleFactory extends Factory
{
    protected $model = Temple::class;

    public function definition(): array
    {
        return [
            'temple_name' => $this->faker->company().' Temple',
            'temple_address' => $this->faker->address(),
            'phone' => $this->faker->unique()->numerify('+91##########'),
            'temple_logo' => null,
            'database_name' => null,
        ];
    }
}
