<?php

namespace Database\Factories;

use App\Models\ProductImage;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'image_url' => '/storage/products/' . $this->faker->uuid . '.jpg',
            'order' => 1,
        ];
    }

}
