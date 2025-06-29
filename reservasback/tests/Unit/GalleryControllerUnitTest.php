<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Gallery;
use App\Http\Controllers\GalleryController;
use Illuminate\Http\JsonResponse;
use Mockery;

class GalleryControllerUnitTest extends TestCase
{
    public function test_index_returns_json_response()
    {
        // Simular datos
        $galleryItems = collect([
            (object)[
                'id' => 1,
                'caption' => 'Foto 1',
                'image_path' => 'gallery/foto1.jpg',
            ],
            (object)[
                'id' => 2,
                'caption' => 'Foto 2',
                'image_path' => 'gallery/foto2.jpg',
            ],
        ]);

        // Mock del modelo
        $mockGallery = Mockery::mock(Gallery::class);
        $mockGallery->shouldReceive('all')->once()->andReturn($galleryItems);

        // Inyectar al controlador
        $controller = new GalleryController($mockGallery);

        // Ejecutar el método
        $response = $controller->index();

        // Validaciones
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('Foto 1', $response->getContent());
        $this->assertStringContainsString('Foto 2', $response->getContent());
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
