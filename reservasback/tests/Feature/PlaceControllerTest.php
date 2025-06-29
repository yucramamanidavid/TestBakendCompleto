<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Place;

class PlaceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_places()
    {
        Place::create([
            'name' => 'Lago Titicaca',
            'excerpt' => 'Lugar turístico',
            'activities' => ['bote', 'pesca'],
            'stats' => ['visitas' => 1500],
            'image_url' => null,
            'latitude' => -15.5,
            'longitude' => -70.1,
            'category' => 'Lago'
        ]);

        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Lago Titicaca']);
    }

    public function test_store_creates_place()
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

        $response = $this->postJson('/api/places', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Nuevo Lugar']);
    }

    public function test_show_returns_place()
    {
        $place = Place::create([
            'name' => 'Mirador',
            'excerpt' => 'Vista panorámica',
            'activities' => [],
            'stats' => [],
            'image_url' => null,
            'latitude' => -13.1,
            'longitude' => -74.1,
            'category' => 'Mirador'
        ]);

        $response = $this->getJson("/api/places/{$place->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Mirador']);
    }

    public function test_update_modifies_place()
    {
        $place = Place::create([
            'name' => 'Lugar Antiguo',
            'excerpt' => 'Antiguo texto',
            'activities' => [],
            'stats' => [],
            'image_url' => null,
            'latitude' => 0,
            'longitude' => 0,
            'category' => 'Otro'
        ]);

        $newData = [
            'name' => 'Lugar Actualizado',
            'excerpt' => 'Texto actualizado',
        ];

        $response = $this->putJson("/api/places/{$place->id}", $newData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Lugar Actualizado']);
    }

    public function test_destroy_deletes_place()
    {
        $place = Place::create([
            'name' => 'Temporal',
            'excerpt' => 'Será eliminado',
            'activities' => [],
            'stats' => [],
            'image_url' => null,
            'latitude' => 0,
            'longitude' => 0,
            'category' => 'Temporal'
        ]);

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }
}