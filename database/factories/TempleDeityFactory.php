<?php

namespace Database\Factories;

use App\Models\Temple;
use App\Models\TempleDeity;
use Illuminate\Database\Eloquent\Factories\Factory;

class TempleDeityFactory extends Factory
{
    protected $model = TempleDeity::class;

    public function definition(): array
    {
        $deityPrefixes = ['Lord', 'Goddess', 'Sri', 'Shri'];
        $deityNames = [
            'Ganesha', 'Shiva', 'Parvati', 'Vishnu', 'Lakshmi', 'Krishna',
            'Rama', 'Durga', 'Hanuman', 'Murugan', 'Saraswati', 'Ayyappa',
            'Brahma', 'Kartikeya', 'Narasimha', 'Venkateswara', 'Subramanya',
        ];

        return [
            'temple_id' => Temple::factory(),
            'name' => $this->faker->randomElement($deityPrefixes).' '.$this->faker->randomElement($deityNames),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
