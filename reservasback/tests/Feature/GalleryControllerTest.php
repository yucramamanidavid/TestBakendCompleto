<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Gallery;
use App\Models\User;

class GalleryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Creamos un usuario autenticado
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_index_returns_gallery_items()
    {
        Gallery::create([
            'caption' => 'Vista hermosa',
            'image_path' => 'gallery/sample.jpg',
        ]);

        $response = $this->getJson('/api/gallery');

        $response->assertStatus(200)
                 ->assertJsonFragment(['caption' => 'Vista hermosa']);
    }

    public function test_store_uploads_images()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('foto1.jpg');
        $response = $this->postJson('/api/gallery', [
            'images' => [$image],
            'captions' => ['Foto de prueba']
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['caption' => 'Foto de prueba']);

        Storage::disk('public')->assertExists('gallery/' . $image->hashName());
    }

    public function test_update_changes_caption_and_image()
    {
        Storage::fake('public');

        $oldImage = UploadedFile::fake()->image('old.jpg')->store('gallery', 'public');

        $gallery = Gallery::create([
            'caption' => 'Antigua',
            'image_path' => $oldImage,
        ]);

        $newImage = UploadedFile::fake()->image('nueva.jpg');

        $response = $this->putJson("/api/gallery/{$gallery->id}", [
            'caption' => 'Nueva Caption',
            'image' => $newImage,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['caption' => 'Nueva Caption']);

        Storage::disk('public')->assertExists('gallery/' . $newImage->hashName());
        Storage::disk('public')->assertMissing($oldImage);
    }

    public function test_destroy_deletes_gallery_item_and_image()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('para_borrar.jpg')->store('gallery', 'public');

        $gallery = Gallery::create([
            'caption' => 'Borrar esto',
            'image_path' => $image,
        ]);

        $response = $this->deleteJson("/api/gallery/{$gallery->id}");

        $response->assertStatus(204);
        Storage::disk('public')->assertMissing($image);
        $this->assertDatabaseMissing('galleries', ['id' => $gallery->id]);
    }
}