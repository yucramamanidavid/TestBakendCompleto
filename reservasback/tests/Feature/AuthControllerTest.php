<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'cliente']);
        Role::create(['name' => 'emprendedor']);
        Role::create(['name' => 'super-admin']);
    }

    /** @test */
    public function it_registers_a_new_user()
    {
        Storage::fake('public');

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'cliente',
            'phone' => '123456789',
            'document_id' => '12345678',
            'birth_date' => '2000-01-01',
            'address' => 'Some Address',
            'location' => 'Lima',
            'profile_image' => UploadedFile::fake()->image('avatar.jpg')
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Registro exitoso'])
                 ->assertJsonStructure(['user', 'token', 'roles']);
    }

    /** @test */
    public function it_fails_registration_with_duplicate_email()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'cliente',
        ]);

        $response->assertStatus(422); // Laravel responde 422 a validación fallida
    }

    /** @test */
    public function it_fails_registration_with_invalid_role()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'another@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'invalido',
        ]);
        $response->assertStatus(422); // Valida el campo role
    }

    /** @test */
    public function it_logs_in_user()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('cliente');

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Login exitoso'])
                 ->assertJsonStructure(['user', 'token', 'roles']);
    }

    /** @test */
    public function it_fails_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'loginfail@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('cliente');

        $response = $this->postJson('/api/login', [
            'email' => 'loginfail@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_logs_out_authenticated_user()
    {
        $user = User::factory()->create();
        $user->assignRole('cliente');
        $token = $user->createToken('Token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Sesión cerrada exitosamente']);
    }

    /** @test */
    public function it_requires_authentication_for_logout()
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);
    }

    /** @test */
/** @test */
    public function it_gets_user_role()
    {
        $user = User::factory()->create(['name' => 'Carlos']);
        $user->assignRole('cliente');

        // Autenticar usuario (ejemplo usando Sanctum)
        $token = $user->createToken('Token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson("/api/user/{$user->id}/roles");

        $response->assertStatus(200)
                ->assertJsonFragment(['user' => 'Carlos']);
    }


    /** @test */
/** @test */
    public function it_returns_not_found_for_missing_user_role()
    {
        // Crea un usuario solo para obtener un token válido
        $user = User::factory()->create();
        $token = $user->createToken('Token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/user/9999/roles');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_updates_user_profile()
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Old Name']);
        $user->assignRole('cliente');
        $token = $user->createToken('Token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->putJson('/api/me/update', [
                             'name' => 'New Name',
                             'phone' => '987654321',
                             'address' => 'Updated address',
                             'profile_image' => UploadedFile::fake()->image('profile.jpg'),
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'New Name']);
    }

    /** @test */
    public function it_fails_update_profile_without_auth()
    {
        $response = $this->putJson('/api/me/update', [
            'name' => 'Name',
        ]);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_searches_user_by_email()
    {
        $user = User::factory()->create(['email' => 'search@example.com']);

        $response = $this->getJson('/api/users/search?email=search@example.com');

        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => 'search@example.com']);
    }

    /** @test */
    public function it_returns_not_found_on_search_user()
    {
        $response = $this->getJson('/api/users/search?email=nouser@example.com');
        $response->assertStatus(404);
    }

    /** @test */
    public function it_requires_email_on_search_user()
    {
        $response = $this->getJson('/api/users/search');
        $response->assertStatus(400);
    }
}

