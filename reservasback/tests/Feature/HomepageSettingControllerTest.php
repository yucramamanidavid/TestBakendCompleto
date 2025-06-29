<?php

namespace Tests\Feature;

use App\Models\HomepageSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_listar_todas_las_configuraciones()
    {
        HomepageSetting::create(['title_text' => 'Texto1', 'active' => false]);
        HomepageSetting::create(['title_text' => 'Texto2', 'active' => true]);

        $response = $this->get('/api/home/all');
        $response->assertStatus(200);
        $response->assertJsonFragment(['title_text' => 'Texto1']);
        $response->assertJsonFragment(['title_text' => 'Texto2']);
    }

    /** @test */
    public function test_obtiene_configuracion_activa()
    {
        HomepageSetting::create(['title_text' => 'Texto Inactivo', 'active' => false]);
        $active = HomepageSetting::create(['title_text' => 'Texto Activo', 'active' => true]);

        $response = $this->get('/api/home/active');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $active->id, 'title_text' => 'Texto Activo']);
    }

    /** @test */
    public function test_crea_una_nueva_configuracion()
    {
        $response = $this->post('/api/home', [
            'title_text' => 'Nuevo título',
            'description' => 'Descripción',
            'title_color' => '#000000',
            'background_color' => '#FFFFFF',
        ]);
        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Configuración creada.']);
        $this->assertDatabaseHas('homepage_settings', ['title_text' => 'Nuevo título']);
    }

    /** @test */
    public function test_actualiza_una_configuracion()
    {
        $setting = HomepageSetting::create([
            'title_text' => 'Antiguo',
            'active' => false
        ]);

        $response = $this->put("/api/home/{$setting->id}", [
            'title_text' => 'Actualizado',
            'background_color' => '#123456',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('homepage_settings', [
            'id' => $setting->id,
            'title_text' => 'Actualizado',
            'background_color' => '#123456',
        ]);
    }

    /** @test */
    public function test_activa_una_configuracion_por_api()
    {
        $setting1 = HomepageSetting::create(['title_text' => 'Uno', 'active' => true]);
        $setting2 = HomepageSetting::create(['title_text' => 'Dos', 'active' => false]);

        $response = $this->post("/api/home/{$setting2->id}/activate");
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Configuración activada correctamente.']);
        $this->assertDatabaseHas('homepage_settings', ['id' => $setting2->id, 'active' => true]);
        $this->assertDatabaseHas('homepage_settings', ['id' => $setting1->id, 'active' => false]);
    }

    /** @test */
    public function test_elimina_una_configuracion()
    {
        $setting = HomepageSetting::create(['title_text' => 'Eliminarme', 'active' => false]);
        $response = $this->delete("/api/home/{$setting->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('homepage_settings', ['id' => $setting->id]);
    }

    /** @test */
    public function test_quita_imagenes_de_la_activa()
    {
        Storage::fake('public');
        $setting = HomepageSetting::create([
            'title_text' => 'Con Imagen',
            'active' => true,
            'image_path' => ['homepage/fake1.jpg', 'homepage/fake2.jpg']
        ]);
        Storage::disk('public')->put('homepage/fake1.jpg', 'contenido1');
        Storage::disk('public')->put('homepage/fake2.jpg', 'contenido2');

        $response = $this->post('/api/home/remove-image');
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Todas las imágenes fueron eliminadas.']);

        // Actualizado: verificación segura para array vacío en DB
        $setting = $setting->fresh();
        $this->assertEquals([], $setting->image_path);
    }
}
