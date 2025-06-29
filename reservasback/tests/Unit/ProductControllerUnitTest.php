<?php

namespace Tests\Unit;

use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Models\User;
use App\Models\Entrepreneur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_retorna_productos_visibles_para_invitado()
    {
        $emprendedorUser = User::create([
            'name' => 'Emprendedor',
            'email' => 'emp@x.com',
            'password' => bcrypt('secret')
        ]);
        $emprendedor = Entrepreneur::create([
            'user_id' => $emprendedorUser->id,
            'business_name' => 'Negocio',
            'phone' => '999999999'
        ]);

        $prod1 = Product::create([
            'name' => 'Producto Público',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 100,
            'is_active' => true
        ]);
        $prod2 = Product::create([
            'name' => 'Producto Oculto',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 150,
            'is_active' => false
        ]);

        // SIN autenticación, debe ver solo el activo
        $controller = new ProductController();
        $response = $controller->index();
        $data = $response->getData(true);

        $ids = array_column($data, 'id');
        $this->assertContains($prod1->id, $ids);
        $this->assertNotContains($prod2->id, $ids);
    }

    /** @test */
    public function index_retorna_productos_para_emprendedor_autenticado()
    {
        // Crea el rol antes de asignarlo
        Role::firstOrCreate(['name' => 'emprendedor', 'guard_name' => 'web']);

        // Creamos usuario y le asignamos el rol de emprendedor
        $user = User::create([
            'name' => 'Emp',
            'email' => 'emp@x.com',
            'password' => bcrypt('secret')
        ]);
        $user->assignRole('emprendedor');

        $emprendedor = Entrepreneur::create([
            'user_id' => $user->id,
            'business_name' => 'Mi negocio',
            'phone' => '123456789'
        ]);
        $prod = Product::create([
            'name' => 'Producto mío',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 99,
            'is_active' => true
        ]);
        // Otro producto, de otro emprendedor
        $otherEmpr = Entrepreneur::create([
            'user_id' => User::create([
                'name' => 'Otro',
                'email' => 'otro@x.com',
                'password' => bcrypt('secret')
            ])->id,
            'business_name' => 'Negocio 2',
            'phone' => '000000000'
        ]);
        $prodAjen = Product::create([
            'name' => 'Producto ajeno',
            'entrepreneur_id' => $otherEmpr->id,
            'price' => 77,
            'is_active' => true
        ]);

        // Mockeamos que el usuario está autenticado
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $controller = new ProductController();
        $response = $controller->index();
        $arr = $response->getData(true);
        $ids = array_column($arr, 'id');
        $this->assertContains($prod->id, $ids); // solo su producto
        $this->assertNotContains($prodAjen->id, $ids); // que no vea el producto ajeno
    }

    /** @test */
    public function byEntrepreneur_retorna_productos_filtrados()
    {
        $emprendedorUser = User::create([
            'name' => 'Emp',
            'email' => 'emp2@x.com',
            'password' => bcrypt('secret')
        ]);
        $emprendedor = Entrepreneur::create([
            'user_id' => $emprendedorUser->id,
            'business_name' => 'Negocio',
            'phone' => '999888777'
        ]);
        $prod = Product::create([
            'name' => 'Producto X',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 123,
            'is_active' => true
        ]);
        $controller = new ProductController();
        $response = $controller->byEntrepreneur($emprendedor->id);
        $arr = $response->getData(true);
        $ids = array_column($arr, 'id');
        $this->assertContains($prod->id, $ids);
    }
}
