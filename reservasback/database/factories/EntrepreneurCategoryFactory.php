<?php

namespace Database\Factories;

use App\Models\Entrepreneur;
use App\Models\Category;
use App\Models\EntrepreneurCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntrepreneurCategoryFactory extends Factory
{
    protected $model = EntrepreneurCategory::class;

    public function definition()
    {
        return [
            'entrepreneur_id' => Entrepreneur::factory(),
            'category_id'     => Category::factory(),
        ];
    }
}
