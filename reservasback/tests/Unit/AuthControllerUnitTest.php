<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_a_user_directly()
    {
        Role::create(['name' => 'cliente']);

        $request = Request::create('/api/register', 'POST', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'role' => 'cliente',
        ]);

        $controller = new AuthController();
        $response = $controller->register($request);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
    }

    /** @test */
/** @test */
    public function it_cannot_register_with_invalid_role()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $request = Request::create('/api/register', 'POST', [
            'name' => 'Ana',
            'email' => 'ana2@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'role' => 'fake',
        ]);

        $controller = new AuthController();
        $controller->register($request); // Aquí es donde se lanzará la excepción
    }


    /** @test */
    public function it_can_get_user_role_directly()
    {
        Role::create(['name' => 'cliente']);
        $user = User::factory()->create(['name' => 'Luis']);
        $user->assignRole('cliente');

        $controller = new AuthController();
        $response = $controller->getUserRole($user->id);

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('Luis', $response->getContent());
    }

    /** @test */
    public function it_returns_404_if_user_role_not_found()
    {
        $controller = new AuthController();
        $response = $controller->getUserRole(9999);
        $this->assertEquals(404, $response->status());
    }
}
