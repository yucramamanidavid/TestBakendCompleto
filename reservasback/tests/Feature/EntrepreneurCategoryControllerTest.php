<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Entrepreneur;
use App\Models\EntrepreneurCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntrepreneurCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_assignments()
    {
        // Crear datos de prueba
        $entrepreneur = Entrepreneur::factory()->create();
        $category = Category::factory()->create();

        EntrepreneurCategory::create([
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/entrepreneur-categories');

        $response->assertOk()
                 ->assertJsonFragment([
                     'entrepreneur_id' => $entrepreneur->id,
                     'category_id' => $category->id,
                 ]);
    }

    /** @test */
    public function it_can_create_an_assignment()
    {
        $entrepreneur = Entrepreneur::factory()->create();
        $category = Category::factory()->create();

        $response = $this->postJson('/api/entrepreneur-categories', [
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category->id,
        ]);

        $response->assertCreated()
                 ->assertJson([
                     'entrepreneur_id' => $entrepreneur->id,
                     'category_id' => $category->id,
                 ]);

        $this->assertDatabaseHas('entrepreneur_categories', [
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function it_can_show_an_assignment()
    {
        $entrepreneur = Entrepreneur::factory()->create();
        $category = Category::factory()->create();

        $assignment = EntrepreneurCategory::create([
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/entrepreneur-categories/{$assignment->entrepreneur_id}/{$assignment->category_id}");

        $response->assertOk()
                 ->assertJson([
                     'entrepreneur_id' => $assignment->entrepreneur_id,
                     'category_id' => $assignment->category_id,
                 ]);
    }

    /** @test */
    public function it_can_update_an_assignment()
    {
        $entrepreneur = Entrepreneur::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $assignment = EntrepreneurCategory::create([
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category1->id,
        ]);

        $response = $this->putJson("/api/entrepreneur-categories/{$assignment->entrepreneur_id}/{$assignment->category_id}", [
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category2->id,
        ]);

        $response->assertOk()
                 ->assertJson([
                     'entrepreneur_id' => $entrepreneur->id,
                     'category_id' => $category2->id,
                 ]);

        $this->assertDatabaseHas('entrepreneur_categories', [
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category2->id,
        ]);
    }

    /** @test */
    public function it_can_delete_an_assignment()
    {
        $entrepreneur = Entrepreneur::factory()->create();
        $category = Category::factory()->create();

        $assignment = EntrepreneurCategory::create([
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/entrepreneur-categories/{$assignment->entrepreneur_id}/{$assignment->category_id}");

        $response->assertOk()
                 ->assertJson([
                     'message' => 'Categoría desasignada del emprendedor',
                 ]);

        $this->assertDatabaseMissing('entrepreneur_categories', [
            'entrepreneur_id' => $entrepreneur->id,
            'category_id' => $category->id,
        ]);
    }
}
