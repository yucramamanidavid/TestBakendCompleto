<?php

namespace Database\Factories;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    public function definition()
    {
        $title = $this->faker->sentence(3);

        return [
            'title'      => $title,
            'slug'       => Str::slug($title) . '-' . $this->faker->unique()->randomNumber(3),
            'category'   => $this->faker->word,
            'icon'       => $this->faker->word,
            'content'    => $this->faker->paragraph,
            'order'      => $this->faker->numberBetween(1, 100),
            'image_url'  => null, // o pon fake si quieres
        ];
    }
}
