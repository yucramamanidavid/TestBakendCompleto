<?php

namespace Tests\Unit;

use App\Http\Controllers\ContactController;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ContactControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_devuelve_contacto_si_existe()
    {
        $contact = Contact::create([
            'address' => 'Calle 1',
            'phone' => '9999999',
            'email' => 'mail@mail.com',
            'facebook' => 'https://facebook.com/abc',
            'instagram' => 'https://instagram.com/abc',
            'google_maps_embed' => '<iframe>...</iframe>'
        ]);

        $controller = new ContactController();
        $response = $controller->index();
        $data = $response->getData(true);

        $this->assertEquals('Calle 1', $data['address']);
        $this->assertEquals('mail@mail.com', $data['email']);
    }

    /** @test */
    public function index_devuelve_contacto_vacio_si_no_existe()
    {
        $controller = new ContactController();
        $response = $controller->index();
        $data = $response->getData(true);

        $this->assertNull($data['id']);
        $this->assertEquals('', $data['address']);
    }
}
