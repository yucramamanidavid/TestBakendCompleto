<?php

namespace Database\Factories;

use App\Models\CustomPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomPackageFactory extends Factory
{
    protected $model = CustomPackage::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'status' => 'borrador',
            'user_id' => User::factory(),
            'total_amount' => 0,
        ];
    }
}
