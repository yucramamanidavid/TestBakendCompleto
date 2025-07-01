<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservationTourExtra>
 */
class ReservationTourExtraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => \App\Models\Reservation::factory(),
            'tour_extra_id' => \App\Models\TourExtra::factory(),
        ];
    }
}
