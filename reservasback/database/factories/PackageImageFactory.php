<?php

namespace Database\Factories;

use App\Models\PackageImage;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageImageFactory extends Factory
{
    protected $model = PackageImage::class;

    public function definition()
    {
        return [
            'package_id' => Package::factory(), // relaciona con un Package
            'image_path' => 'package_images/' . $this->faker->uuid . '.jpg',
        ];
    }
}
