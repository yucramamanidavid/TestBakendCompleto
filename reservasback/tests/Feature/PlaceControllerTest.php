<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Place;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlaceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_places(): void
    {
        Place::factory()->create([
            'name' => 'Lago Titicaca',
            'excerpt' => 'Lugar turístico',
            'activities' => ['bote', 'pesca'],
            'stats' => ['visitas' => 1500],
            'image_url' => null,
            'latitude' => -15.5,
            'longitude' => -70.1,
            'category' => 'Lago',
        ]);

        $response = $this->getJson('/api/places');

        $response->assertOk()
                 ->assertJsonFragment(['name' => 'Lago Titicaca']);
    }

    public function test_store_creates_place(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('foto.jpg');

        $data = [
            'name' => 'Nuevo Lugar',
            'excerpt' => 'Descripción breve',
            'activities' => ['turismo'],
            'stats' => ['valor' => 100],
            'image_file' => $file,
            'latitude' => -13.52,
            'longitude' => -71.97,
            'category' => 'Montaña',
        ];

        $response = $this->postJson('/api/places', $data, ['Accept' => 'application/json']);

        $response->assertCreated()
                 ->assertJsonFragment(['name' => 'Nuevo Lugar']);

        $this->assertDatabaseHas('places', ['name' => 'Nuevo Lugar']);
        Storage::disk('public')->assertExists('places/' . $file->hashName());
    }

    public function test_show_returns_place(): void
    {
        $place = Place::factory()->create([
            'name' => 'Mirador',
            'excerpt' => 'Vista panorámica',
            'activities' => [],
            'stats' => [],
            'image_url' => null,
            'latitude' => -13.1,
            'longitude' => -74.1,
            'category' => 'Mirador',
        ]);

        $response = $this->getJson("/api/places/{$place->id}");

        $response->assertOk()
                 ->assertJsonFragment(['name' => 'Mirador']);
    }

    public function test_update_modifies_place(): void
    {
        $place = Place::factory()->create([
            'name' => 'Lugar Antiguo',
            'excerpt' => 'Antiguo texto',
        ]);

        $newData = [
            'name' => 'Lugar Actualizado',
            'excerpt' => 'Texto actualizado',
        ];

        $response = $this->putJson("/api/places/{$place->id}", $newData, ['Accept' => 'application/json']);

        $response->assertOk()
                 ->assertJsonFragment(['name' => 'Lugar Actualizado']);

        $this->assertDatabaseHas('places', ['name' => 'Lugar Actualizado']);
    }

    public function test_destroy_deletes_place(): void
    {
        $place = Place::factory()->create([
            'name' => 'Temporal',
            'excerpt' => 'Será eliminado',
        ]);

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }
}
