<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_all_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_creates_a_category()
    {
        $data = [
            'name' => 'Aventura',
            'icon' => 'fa-tree'
        ];

        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Aventura', 'icon' => 'fa-tree']);

        $this->assertDatabaseHas('categories', ['name' => 'Aventura']);
    }

    /** @test */
    public function it_validates_category_store()
    {
        // Intenta crear sin nombre
        $response = $this->postJson('/api/categories', ['icon' => 'fa-leaf']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_shows_a_category_with_entrepreneurs()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $category->id]);
    }

    /** @test */
    public function it_updates_a_category()
    {
        $category = Category::factory()->create(['name' => 'Viejo']);

        $data = ['name' => 'Nuevo nombre', 'icon' => 'fa-mountain'];

        $response = $this->putJson("/api/categories/{$category->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Nuevo nombre']);

        $this->assertDatabaseHas('categories', ['name' => 'Nuevo nombre']);
    }

    /** @test */
    public function it_deletes_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Categoría eliminada']);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_fails_to_create_duplicate_category()
    {
        Category::factory()->create(['name' => 'Ecoturismo']);
        $data = [
            'name' => 'Ecoturismo',
            'icon' => 'fa-leaf'
        ];

        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }
}
