<?php

namespace Tests\Unit;

use App\Http\Controllers\CategoryController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_a_category()
    {
        $request = Request::create('/api/categories', 'POST', [
            'name' => 'Gastronómica',
            'icon' => 'fa-utensils'
        ]);

        $controller = new CategoryController();
        $response = $controller->store($request);

        $this->assertDatabaseHas('categories', ['name' => 'Gastronómica']);
        $this->assertEquals('Gastronómica', $response->getData()->name);
    }

    /** @test */
    public function it_updates_a_category()
    {
        $category = Category::factory()->create(['name' => 'Original']);

        $request = Request::create('', 'PUT', [
            'name' => 'Modificada'
        ]);

        $controller = new CategoryController();
        $response = $controller->update($request, $category);

        $this->assertEquals('Modificada', $response->getData()->name);
        $this->assertDatabaseHas('categories', ['name' => 'Modificada']);
    }
}
