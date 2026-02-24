<?php

namespace Database\Factories;

use App\Models\Temple;
use App\Models\TemplePooja;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemplePoojaFactory extends Factory
{
    protected $model = TemplePooja::class;

    public function definition(): array
    {
        $poojaNames = [
            'Ganapathi Homam', 'Navagraha Pooja', 'Lakshmi Pooja',
            'Saraswati Pooja', 'Durga Pooja', 'Shiva Abhishekam',
            'Vishnu Sahasranama', 'Hanuman Chalisa', 'Rudra Abhishekam',
        ];

        return [
            'temple_id' => Temple::factory(),
            'member_id' => null,
            'pooja_name' => $this->faker->randomElement($poojaNames),
            'period' => $this->faker->randomElement(['once', 'daily', 'monthly', 'yearly']),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'details' => $this->faker->sentence(),
            'devotees_required' => $this->faker->numberBetween(1, 5),
            'next_pooja_perform_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
