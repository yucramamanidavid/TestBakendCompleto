<?php

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExperienceControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_listar_experiencias()
    {
        Experience::factory()->create(['title' => 'Primera', 'order' => 2]);
        Experience::factory()->create(['title' => 'Segunda', 'order' => 1]);

        $response = $this->getJson('/api/experiences');
        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment(['title' => 'Primera'])
                 ->assertJsonFragment(['title' => 'Segunda']);
    }

    /** @test */
    public function puede_crear_experiencia_con_imagen()
    {
        Storage::fake('public');
        $data = [
            'title'    => 'Test Exp',
            'slug'     => 'test-exp',
            'category' => 'Test',
            'icon'     => 'fa-icon',
            'content'  => 'Contenido',
            'image'    => UploadedFile::fake()->image('exp.jpg'),
        ];

        $response = $this->postJson('/api/experiences', $data);
        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Test Exp']);

        $this->assertDatabaseHas('experiences', ['title' => 'Test Exp']);
        Storage::disk('public')->assertExists('experiences/' . basename($response->json('image_url')));
    }

    /** @test */
    public function test_puede_actualizar_experiencia_con_imagen_nueva()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // <--- AUTENTICACIÓN

        $exp = Experience::factory()->create();
        $data = [
            'title'    => 'ExpUpdated',
            'slug'     => 'expupdated',
            'category' => 'cat2',
            'content'  => 'Nuevo contenido',
            'image'    => UploadedFile::fake()->image('nueva.jpg'),
        ];

        $response = $this->putJson("/api/experiences/{$exp->id}", $data);
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'ExpUpdated']);
    }

    /** @test */
    public function test_puede_eliminar_experiencia()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); // <--- AUTENTICACIÓN

        $exp = Experience::factory()->create();

        $response = $this->deleteJson("/api/experiences/{$exp->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('experiences', ['id' => $exp->id]);
    }
}
