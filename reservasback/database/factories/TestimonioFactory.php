<?php

namespace Database\Factories;

use App\Models\Testimonio;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonioFactory extends Factory
{
    protected $model = Testimonio::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->name,
            'estrellas' => $this->faker->numberBetween(1, 5),
            'comentario' => $this->faker->sentence,
            'user_id' => null,
        ];
    }
}
