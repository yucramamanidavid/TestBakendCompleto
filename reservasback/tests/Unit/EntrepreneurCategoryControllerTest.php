<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\EntrepreneurCategoryController;
use App\Models\EntrepreneurCategory;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EntrepreneurCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_assignments()
    {
        // Arrange
        EntrepreneurCategory::factory()->count(3)->create();
        $controller = new EntrepreneurCategoryController();

        // Act
        $response = $controller->index();

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertCount(3, $response->getData(true));
    }

    /** @test */
public function it_can_create_an_assignment()
{
    $controller = new EntrepreneurCategoryController();

    // Creamos usuario de prueba
    \DB::table('users')->insert([
        'id' => 1,
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => bcrypt('password')
    ]);

    // Creamos emprendedor con campos obligatorios
    \DB::table('entrepreneurs')->insert([
        'id' => 1,
        'user_id' => 1,
        'business_name' => 'Test Business',
        'phone' => '999999999'
    ]);

    // Creamos categoría con campo obligatorio 'name'
    \DB::table('categories')->insert([
        'id' => 2,
        'name' => 'Test Category'
    ]);

    $request = Request::create('/api/entrepreneur-categories', 'POST', [
        'entrepreneur_id' => 1,
        'category_id' => 2
    ]);

    $response = $controller->store($request);

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(201, $response->status());
    $this->assertEquals(1, EntrepreneurCategory::count());
}


    /** @test */
    public function it_can_count_categories()
    {
        \DB::table('categories')->insert(['id' => 1, 'name' => 'Category 1']);
        \DB::table('categories')->insert(['id' => 2, 'name' => 'Category 2']);

        $controller = new EntrepreneurCategoryController();

        $response = $controller->count();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(2, $response->getData()->count);
    }
}
