<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Entrepreneur;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_listar_productos_publicos()
    {
        $user = User::create([
            'name' => 'Emp',
            'email' => 'emp1@x.com',
            'password' => bcrypt('secret')
        ]);
        $emprendedor = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Negocio',
            'phone' => '999999999'
        ]);
        $prod = Product::create([
            'name' => 'Producto público',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 45,
            'is_active' => true
        ]);
        $prod2 = Product::create([
            'name' => 'Producto oculto',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 33,
            'is_active' => false
        ]);

        $response = $this->get('/api/products');
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $prod->id])
            ->assertJsonMissing(['id' => $prod2->id]);
    }

    /** @test */
    public function puede_ver_un_producto_detallado()
    {
        $user = User::create([
            'name' => 'Emp',
            'email' => 'emp2@x.com',
            'password' => bcrypt('secret')
        ]);
        $emprendedor = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Negocio2',
            'phone' => '123123123'
        ]);
        $prod = Product::create([
            'name' => 'Producto detallado',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 55,
            'is_active' => true
        ]);
        $response = $this->get("/api/products/{$prod->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $prod->id]);
    }

    /** @test */
    public function crea_producto_con_imagenes_y_categorias()
    {
        Storage::fake('public');
        $user = User::create([
            'name' => 'Emp',
            'email' => 'emp3@x.com',
            'password' => bcrypt('secret')
        ]);
        $emprendedor = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Negocio3',
            'phone' => '222222222'
        ]);
        $cat1 = Category::create(['name' => 'Cat1']);
        $cat2 = Category::create(['name' => 'Cat2']);

        $mainImage = UploadedFile::fake()->image('main.jpg');
        $img1 = UploadedFile::fake()->image('img1.jpg');
        $img2 = UploadedFile::fake()->image('img2.jpg');

        $payload = [
            'entrepreneur_id' => $emprendedor->id,
            'name' => 'Test product',
            'price' => 44,
            'is_active' => true,
            'category_ids' => [$cat1->id, $cat2->id],
            'main_image' => $mainImage,
            'images' => [$img1, $img2],
        ];

        $this->actingAs($user, 'sanctum');

        $response = $this->post('/api/products', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Producto creado exitosamente']);
        $this->assertDatabaseHas('products', ['name' => 'Test product']);
    }

    /** @test */
    public function actualiza_producto_y_reemplaza_imagenes()
    {
        Storage::fake('public');
        $user = User::create([
            'name' => 'Emp',
            'email' => 'emp4@x.com',
            'password' => bcrypt('secret')
        ]);
        $emprendedor = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Negocio4',
            'phone' => '333333333'
        ]);
        $cat1 = Category::create(['name' => 'CatA']);
        $prod = Product::create([
            'name' => 'Producto a actualizar',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 120,
            'is_active' => true
        ]);
        $imgPrev = ProductImage::create([
            'product_id' => $prod->id,
            'image_url' => '/storage/products/old.jpg',
            'order' => 0
        ]);

        $img1 = UploadedFile::fake()->image('nuevo1.jpg');
        $img2 = UploadedFile::fake()->image('nuevo2.jpg');

        $payload = [
            'name' => 'Actualizado',
            'price' => 125,
            'category_ids' => [$cat1->id],
            'images' => [$img1, $img2],
        ];

        $this->actingAs($user, 'sanctum');

        $response = $this->put("/api/products/{$prod->id}", $payload);
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Producto actualizado']);
        $this->assertDatabaseHas('products', ['name' => 'Actualizado', 'price' => 125]);
    }

    /** @test */
    public function elimina_un_producto_y_sus_imagenes()
    {
        Storage::fake('public');
        $user = User::create([
            'name' => 'Emp',
            'email' => 'emp5@x.com',
            'password' => bcrypt('secret')
        ]);
        $emprendedor = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Negocio5',
            'phone' => '444444444'
        ]);
        $prod = Product::create([
            'name' => 'A borrar',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 14,
            'is_active' => true
        ]);
        $img = ProductImage::create([
            'product_id' => $prod->id,
            'image_url' => '/storage/products/borrar.jpg',
            'order' => 0
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->delete("/api/products/{$prod->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Producto eliminado']);
        $this->assertDatabaseMissing('products', ['id' => $prod->id]);
        $this->assertDatabaseMissing('product_images', ['id' => $img->id]);
    }

    /** @test */
    public function puede_agregar_y_eliminar_imagen_individual()
    {
        Storage::fake('public');
        $user = User::create([
            'name' => 'Emp',
            'email' => 'emp6@x.com',
            'password' => bcrypt('secret')
        ]);
        $emprendedor = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Negocio6',
            'phone' => '555555555'
        ]);
        $prod = Product::create([
            'name' => 'Producto Imagen',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 15,
            'is_active' => true
        ]);
        $img = UploadedFile::fake()->image('extra.jpg');

        $this->actingAs($user, 'sanctum');

        $respAdd = $this->post("/api/products/{$prod->id}/images", [
            'image' => $img,
            'order' => 7
        ]);
        $respAdd->assertStatus(201)
            ->assertJsonFragment(['order' => 7]);

        $newImgId = $respAdd->json('id');
        $respDel = $this->delete("/api/products/{$prod->id}/images/{$newImgId}");
        $respDel->assertStatus(204);
        $this->assertDatabaseMissing('product_images', ['id' => $newImgId]);
    }
    public function test_puede_listar_productos_publicos()
    {
        $prod = Product::factory()->create(['is_active' => true]);
        $prod2 = Product::factory()->create(['is_active' => false]);

        $response = $this->get('/api/products');
        $response->assertStatus(200)
                ->assertJsonFragment(['id' => $prod->id])
                ->assertJsonMissing(['id'    => $prod2->id]);
    }

}
