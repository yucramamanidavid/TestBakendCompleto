<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\CustomPackage;
use App\Models\Entrepreneur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomPackageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_custom_packages()
    {
        $user = User::create([
            'name' => 'Cliente 1',
            'email' => 'cliente1@example.com',
            'password' => bcrypt('secret'),
        ]);

        CustomPackage::create([
            'user_id' => $user->id,
            'name' => 'Mi Paquete',
            'status' => 'confirmado',
            'total_amount' => 50,
        ]);

        $this->actingAs($user)
            ->getJson('/api/custom-packages')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Mi Paquete']);
    }

    public function test_user_can_create_a_custom_package()
    {
        $user = User::create([
            'name' => 'Cliente 2',
            'email' => 'cliente2@example.com',
            'password' => bcrypt('secret'),
        ]);

        $entrepreneur = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Negocio Test',
            'phone' => '999999999',
            'document_id' => '12345678',
            'address' => 'Dirección prueba',
        ]);

        $product = Product::create([
            'name' => 'Prod 1',
            'price' => 10,
            'entrepreneur_id' => $entrepreneur->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/custom-packages', [
                'name' => 'Paquete Personalizado',
                'status' => 'confirmado',
                'products' => [
                    ['id' => $product->id, 'quantity' => 2]
                ],
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Paquete Personalizado']);
    }

    public function test_user_can_update_their_package()
    {
        $user = User::create([
            'name' => 'Cliente 3',
            'email' => 'cliente3@example.com',
            'password' => bcrypt('secret'),
        ]);

        $entrepreneur = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Negocio X',
            'phone' => '988888888',
            'document_id' => '87654321',
            'address' => 'Dirección X',
        ]);

        $product = Product::create([
            'name' => 'Producto',
            'price' => 20,
            'entrepreneur_id' => $entrepreneur->id,
        ]);

        $package = CustomPackage::create([
            'user_id' => $user->id,
            'name' => 'Antiguo Paquete',
            'status' => 'borrador',
            'total_amount' => 0,
        ]);

        $this->actingAs($user)
            ->putJson("/api/custom-packages/{$package->id}", [
                'name' => 'Nuevo Nombre',
                'status' => 'confirmado',
                'products' => [
                    ['id' => $product->id, 'quantity' => 3]
                ],
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nuevo Nombre']);
    }

    public function test_user_can_delete_their_package()
    {
        $user = User::create([
            'name' => 'Cliente 4',
            'email' => 'cliente4@example.com',
            'password' => bcrypt('secret'),
        ]);

        $package = CustomPackage::create([
            'user_id' => $user->id,
            'name' => 'Paquete a eliminar',
            'status' => 'borrador',
            'total_amount' => 0,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/custom-packages/{$package->id}")
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Paquete eliminado']);
    }

    public function test_user_cannot_access_others_package()
    {
        $user1 = User::create([
            'name' => 'User Uno',
            'email' => 'uno@example.com',
            'password' => bcrypt('secret'),
        ]);

        $user2 = User::create([
            'name' => 'User Dos',
            'email' => 'dos@example.com',
            'password' => bcrypt('secret'),
        ]);

        $package = CustomPackage::create([
            'user_id' => $user1->id,
            'name' => 'Privado',
            'status' => 'borrador',
            'total_amount' => 0,
        ]);

        $this->actingAs($user2)
            ->getJson("/api/custom-packages/{$package->id}")
            ->assertStatus(404);
    }
}