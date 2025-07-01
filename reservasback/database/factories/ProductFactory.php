<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Product;
use App\Models\Entrepreneur;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'entrepreneur_id' => Entrepreneur::factory(),
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'stock' => $this->faker->numberBetween(1, 10),
            'duration' => '1h',
            'main_image' => null,
            'is_active' => true,
            'user_id' => User::factory(), 
        ];
    }
}
