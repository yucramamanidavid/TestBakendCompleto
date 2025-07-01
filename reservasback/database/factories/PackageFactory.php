<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Entrepreneur;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 100, 500),
            'entrepreneur_id' => Entrepreneur::factory(),
        ];
    }
}
