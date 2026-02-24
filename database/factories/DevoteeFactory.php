<?php

namespace Database\Factories;

use App\Models\Devotee;
use App\Models\Temple;
use Illuminate\Database\Eloquent\Factories\Factory;

class DevoteeFactory extends Factory
{
    protected $model = Devotee::class;

    public function definition(): array
    {
        $nakshatras = [
            'Ashwini', 'Bharani', 'Krittika', 'Rohini', 'Mrigashira',
            'Ardra', 'Punarvasu', 'Pushya', 'Ashlesha', 'Magha',
            'Purva Phalguni', 'Uttara Phalguni', 'Hasta', 'Chitra',
            'Swati', 'Vishakha', 'Anuradha', 'Jyeshtha', 'Mula',
            'Purva Ashadha', 'Uttara Ashadha', 'Shravana', 'Dhanishta',
            'Shatabhisha', 'Purva Bhadrapada', 'Uttara Bhadrapada', 'Revati',
        ];

        return [
            'temple_id' => Temple::factory(),
            'devotee_name' => $this->faker->name(),
            'devotee_phone' => $this->faker->unique()->numerify('+91##########'),
            'nakshatra' => $this->faker->randomElement($nakshatras),
            'address' => $this->faker->address(),
            'device_created_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
