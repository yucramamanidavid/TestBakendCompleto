<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Http\Controllers\Api\PlaceController;
use Mockery;

class PlaceControllerUnitTest extends TestCase
{
    public function test_index_returns_json_response()
    {
        // Simula una colección de lugares
        $mockPlaces = new Collection([
            new Place(['name' => 'Sitio 1']),
            new Place(['name' => 'Sitio 2']),
        ]);

        // Mock del modelo Place
        $placeMock = Mockery::mock(Place::class);
        $placeMock->shouldReceive('orderBy')
                  ->with('created_at', 'desc')
                  ->andReturnSelf();
        $placeMock->shouldReceive('get')
                  ->andReturn($mockPlaces);

        // Controlador con el mock inyectado
        $controller = new PlaceController($placeMock);
        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Sitio 1', $response->getContent());
        $this->assertStringContainsString('Sitio 2', $response->getContent());
    }
}
