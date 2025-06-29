<?php

namespace Database\Factories;

use App\Models\Entrepreneur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntrepreneurFactory extends Factory
{
    protected $model = Entrepreneur::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'business_name' => $this->faker->company,
            'phone' => $this->faker->regexify('[0-9]{9}'),

        ];
    }
}
