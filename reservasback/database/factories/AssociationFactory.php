<?php

namespace Database\Factories;

use App\Models\Association;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssociationFactory extends Factory
{
    protected $model = Association::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            // añade aquí más campos según tu tabla
        ];
    }
}
