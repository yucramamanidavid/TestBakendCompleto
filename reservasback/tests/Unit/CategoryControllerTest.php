<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\CategoryController;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Mockery;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_categories()
    {
        Category::factory()->count(2)->create();

        $controller = new CategoryController();
        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertCount(2, $response->getData());
    }

    /** @test */
    public function store_creates_a_new_category()
    {
        $controller = new CategoryController();

        $request = Request::create('/api/categories', 'POST', [
            'name' => 'Test Category',
            'icon' => 'test-icon'
        ]);

        $response = $controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    /** @test */
    public function show_returns_a_category_with_entrepreneurs()
    {
        $category = Category::factory()->create();

        $controller = new CategoryController();
        $response = $controller->show($category);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($category->id, $response->getData()->id);
    }

    /** @test */
    public function update_modifies_an_existing_category()
    {
        $category = Category::factory()->create([
            'name' => 'Old Name',
            'icon' => 'old-icon'
        ]);

        $controller = new CategoryController();

        $request = Request::create('/api/categories/' . $category->id, 'PUT', [
            'name' => 'Updated Name',
            'icon' => 'updated-icon'
        ]);

        $response = $controller->update($request, $category);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('Updated Name', $response->getData()->name);
    }

    /** @test */
    public function destroy_deletes_a_category()
    {
        $category = Category::factory()->create();

        $controller = new CategoryController();
        $response = $controller->destroy($category);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
