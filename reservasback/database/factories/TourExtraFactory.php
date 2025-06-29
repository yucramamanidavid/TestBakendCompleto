<?php

namespace Database\Factories;

use App\Models\TourExtra;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

class TourExtraFactory extends Factory
{
    protected $model = TourExtra::class;

    public function definition()
    {
        return [
            'tour_id' => Tour::factory(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
