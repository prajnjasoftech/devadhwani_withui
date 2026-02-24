<?php

namespace Database\Factories;

use App\Models\Temple;
use App\Models\TemplePooja;
use App\Models\TemplePoojaBooking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TemplePoojaBookingFactory extends Factory
{
    protected $model = TemplePoojaBooking::class;

    public function definition(): array
    {
        $bookingDate = $this->faker->dateTimeBetween('now', '+1 month');
        $period = $this->faker->randomElement(['once', 'daily', 'monthly', 'yearly']);

        $endDate = match ($period) {
            'once' => $bookingDate,
            'daily' => (clone $bookingDate)->modify('+7 days'),
            'monthly' => (clone $bookingDate)->modify('+6 months'),
            'yearly' => (clone $bookingDate)->modify('+2 years'),
        };

        return [
            'temple_id' => Temple::factory(),
            'pooja_id' => TemplePooja::factory(),
            'member_id' => null,
            'devotee_id' => null,
            'booking_number' => 'BKG-'.strtoupper(Str::random(8)),
            'booking_date' => $bookingDate,
            'booking_end_date' => $endDate,
            'booking_time_slot' => $this->faker->optional()->time('H:i'),
            'period' => $period,
            'pooja_amount' => $this->faker->randomFloat(2, 100, 5000),
            'service_charge' => $this->faker->randomFloat(2, 0, 100),
            'payment_status' => $this->faker->randomElement(['pending', 'success', 'failed', 'refunded']),
            'payment_mode' => $this->faker->optional()->randomElement(['cash', 'online', 'upi', 'card']),
            'transaction_id' => $this->faker->optional()->uuid(),
            'booking_status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled', 'completed']),
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => 'monthly',
            'booking_end_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => 'yearly',
            'booking_end_date' => now()->addYears(2)->format('Y-m-d'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }
}
