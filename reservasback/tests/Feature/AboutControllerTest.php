<?php

namespace Tests\Feature;

use App\Models\About;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AboutControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_listar_abouts()
    {
        About::factory()->create(['title' => 'A', 'active' => false]);
        About::factory()->create(['title' => 'B', 'active' => true]);

        $response = $this->getJson('/api/abouts');
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'A'])
            ->assertJsonFragment(['title' => 'B']);
    }

    /** @test */
    public function puede_ver_un_about_detallado()
    {
        $about = About::factory()->create([
            'title' => 'Detalle',
            'content' => 'Texto',
            'active' => true,
        ]);
        $response = $this->getJson("/api/abouts/{$about->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Detalle']);
    }

    /** @test */
    public function puede_crear_about_con_imagen()
    {
        Storage::fake('public');
        $data = [
            'title' => 'Nuevo About',
            'content' => 'Contenido About',
            'image' => UploadedFile::fake()->image('foto.jpg')
        ];
        $response = $this->postJson('/api/abouts', $data);
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Nuevo About']);

        $this->assertDatabaseHas('abouts', ['title' => 'Nuevo About']);
        $about = About::where('title', 'Nuevo About')->first();
        Storage::disk('public')->assertExists($about->image);
    }

    /** @test */
    public function puede_actualizar_about_con_nueva_imagen()
    {
        Storage::fake('public');
        $about = About::factory()->create(['image' => null]);
        $data = [
            'title' => 'Editado',
            'content' => 'Editado texto',
            'image' => UploadedFile::fake()->image('nueva.jpg'),
        ];
        $response = $this->putJson("/api/abouts/{$about->id}", $data);
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Editado']);
        $about->refresh();
        Storage::disk('public')->assertExists($about->image);
    }

    /** @test */
    public function puede_eliminar_about_y_su_imagen()
    {
        Storage::fake('public');
        $about = About::factory()->create([
            'image' => 'abouts/borrar.jpg'
        ]);
        Storage::disk('public')->put('abouts/borrar.jpg', 'contenido');

        $response = $this->deleteJson("/api/abouts/{$about->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('abouts', ['id' => $about->id]);
        Storage::disk('public')->assertMissing('abouts/borrar.jpg');
    }

    /** @test */
    public function puede_activar_un_about_y_desactivar_los_anteriores()
    {
        $about1 = About::factory()->create(['active' => false]);
        $about2 = About::factory()->create(['active' => false]);
        $response = $this->postJson("/api/abouts/{$about2->id}/activate");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $about2->id, 'active' => true]);
        $about1->refresh();
        $about2->refresh();
        $this->assertFalse($about1->active);
        $this->assertTrue($about2->active);
    }

    /** @test */
    public function puede_obtener_about_activo()
    {
        About::factory()->create(['active' => false]);
        $about = About::factory()->create(['active' => true]);
        $response = $this->getJson("/api/abouts/active");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $about->id, 'active' => true]);
    }
}
