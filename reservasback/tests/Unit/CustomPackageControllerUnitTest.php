<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CustomPackage;
use App\Models\User;
use App\Http\Controllers\CustomPackageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;

class CustomPackageControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_user_packages()
    {
        // Usuario autenticado
        $user = User::factory()->create();
        $this->actingAs($user);

        // Paquetes del usuario autenticado
        CustomPackage::factory()->create(['name' => 'Paquete A', 'user_id' => $user->id]);
        CustomPackage::factory()->create(['name' => 'Paquete B', 'user_id' => $user->id]);

        // Otro usuario
        $otherUser = User::factory()->create();
        CustomPackage::factory()->create(['name' => 'Paquete Otro', 'user_id' => $otherUser->id]);

        // Instanciar el controlador
        $controller = new CustomPackageController();
        $response = $controller->index();

        // Verificaciones
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('Paquete A', $response->getContent());
        $this->assertStringContainsString('Paquete B', $response->getContent());
        $this->assertStringNotContainsString('Paquete Otro', $response->getContent());
    }
}
