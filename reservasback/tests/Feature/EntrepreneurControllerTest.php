<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Entrepreneur;
use App\Models\Product;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EntrepreneurControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear el rol emprendedor si no existe
        if (!Role::where('name', 'emprendedor')->exists()) {
            Role::create(['name' => 'emprendedor']);
        }

        // Limpiar caché de permisos
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @test */
    public function it_can_list_entrepreneurs()
    {
        Entrepreneur::factory()->count(3)->create();

        $response = $this->getJson('/api/entrepreneurs');

        $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'user_id', 'business_name']
            ]
        ]);


    }

    /** @test */
    public function admin_can_create_an_entrepreneur()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'business_name' => 'Test Business',
            'phone' => '999999999',
        ];

        $response = $this->postJson('/api/entrepreneurs', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure(['entrepreneur_id', 'user_id', 'password']);
    }

    /** @test */
    public function it_can_show_an_entrepreneur()
    {
        $entrepreneur = Entrepreneur::factory()->create();

        $response = $this->getJson('/api/entrepreneurs/' . $entrepreneur->id);

        $response->assertOk()
                 ->assertJsonStructure(['id', 'user_id', 'business_name']);
    }

    /** @test */
    public function it_can_update_an_entrepreneur()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entrepreneur = Entrepreneur::factory()->create();

        $data = ['business_name' => 'Updated Business'];

        $response = $this->putJson('/api/entrepreneurs/' . $entrepreneur->id, $data);

        $response->assertOk()
                 ->assertJsonFragment(['business_name' => 'Updated Business']);
    }

    /** @test */
    public function it_can_delete_an_entrepreneur()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entrepreneur = Entrepreneur::factory()->create();

        $response = $this->deleteJson('/api/entrepreneurs/' . $entrepreneur->id);

        $response->assertOk()
                 ->assertJson(['message' => 'Emprendedor eliminado']);
    }

    /** @test */
    public function it_can_toggle_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entrepreneur = Entrepreneur::factory()->create(['status' => 'activo']);

        $response = $this->putJson('/api/entrepreneurs/' . $entrepreneur->id . '/toggle-status');

        $response->assertOk()
                 ->assertJsonFragment(['status' => 'suspendido']);
    }

    /** @test */
    public function authenticated_user_can_get_own_profile()
    {
        $user = User::factory()->create();
        Entrepreneur::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->getJson('/api/entrepreneur/authenticated');

        $response->assertOk()
                 ->assertJsonStructure(['entrepreneur']);
    }

    /** @test */
    public function authenticated_user_can_get_own_products()
    {
        $user = User::factory()->create();
        $entrepreneur = Entrepreneur::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['entrepreneur_id' => $entrepreneur->id]);

        $this->actingAs($user);

        $response = $this->getJson('/api/products/my');

        $response->assertOk()
                 ->assertJsonFragment(['id' => $product->id]);
    }
}
