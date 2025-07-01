<?php

namespace Database\Factories;

use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlaceFactory extends Factory
{
    use HasFactory;
    protected $model = Place::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
            'excerpt' => $this->faker->sentence(6),
            'activities' => [$this->faker->word, $this->faker->word],
            'stats' => [
                'visits' => $this->faker->numberBetween(0, 1000),
                'rating' => $this->faker->randomFloat(2, 1, 5),
            ],
            'image_url' => $this->faker->imageUrl(640, 480, 'nature', true),
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'category' => $this->faker->word,
        ];
    }
}
