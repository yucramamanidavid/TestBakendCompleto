<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create(); // Asegúrate que este usuario tenga permisos si hay restricciones
        $this->actingAs($user, 'sanctum'); // Si usas Sanctum
        return $user;
    }

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
        $this->authenticate();

        $data = [
            'name' => 'New Category',
            'icon' => 'icon-name'
        ];

        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'New Category']);
        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }

    /** @test */
    public function it_shows_a_category_with_entrepreneurs()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'name', 'icon', 'entrepreneurs']);
    }

    /** @test */
    public function it_updates_a_category()
    {
        $this->authenticate();

        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Name',
            'icon' => 'new-icon'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('categories', ['name' => 'Updated Name']);
    }

    /** @test */
    public function it_deletes_a_category()
    {
        $this->authenticate();

        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Categoría eliminada']);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        $this->authenticate();

        $response = $this->postJson('/api/categories', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_uniqueness_of_name_when_creating()
    {
        $this->authenticate();

        Category::factory()->create(['name' => 'Unique']);

        $response = $this->postJson('/api/categories', [
            'name' => 'Unique'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }
}
