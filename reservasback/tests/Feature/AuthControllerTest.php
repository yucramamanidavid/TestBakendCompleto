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
                 ->assertJsonFragment(['message' => 'Registro exitoso']);
    }

    /** @test */
    public function it_logs_in_user()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password'),
        ])->assignRole('cliente');

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Login exitoso']);
    }

    /** @test */
    public function it_logs_out_authenticated_user()
    {
        $user = User::factory()->create()->assignRole('cliente');
        $token = $user->createToken('Token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Sesión cerrada exitosamente']);
    }

    /** @test */
    public function it_gets_user_role()
    {
        $user = User::factory()->create(['name' => 'Carlos'])->assignRole('cliente');

        $response = $this->getJson("/api/user/role/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['user' => 'Carlos']);
    }

    /** @test */
    public function it_updates_user_profile()
    {
        $user = User::factory()->create(['name' => 'Old Name'])->assignRole('cliente');
        $token = $user->createToken('Token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->putJson('/api/user/update', [
                             'name' => 'New Name',
                             'phone' => '987654321',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'New Name']);
    }

    /** @test */
    public function it_searches_user_by_email()
    {
        $user = User::factory()->create(['email' => 'search@example.com']);

        $response = $this->getJson('/api/user/search?email=search@example.com');

        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => 'search@example.com']);
    }
}
