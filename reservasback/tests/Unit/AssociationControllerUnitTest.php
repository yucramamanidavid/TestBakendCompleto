<?php

namespace Tests\Unit;

use App\Http\Controllers\AssociationController;
use App\Models\Association;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\TestCase;

class AssociationControllerUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function index_returns_all_associations()
    {
        $fakeAssociations = collect([
            (object)['id' => 1, 'name' => 'A1'],
            (object)['id' => 2, 'name' => 'A2'],
        ]);

        $associationMock = Mockery::mock(Association::class);
        $associationMock->shouldReceive('all')->once()->andReturn($fakeAssociations);

        $controller = new AssociationController($associationMock);

        $response = $controller->index();

        $this->assertEquals(200, $response->status());
        $this->assertEquals($fakeAssociations->toJson(), $response->getContent());
    }

    /** @test */
    public function store_validates_and_creates_association()
    {
        $requestData = [
            'name' => 'Nueva Asociación',
            'description' => 'Descripción',
            'region' => 'Cusco',
        ];

        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('validate')->once()->andReturn($requestData);

        $fakeAssociation = (object) array_merge(['id' => 10], $requestData);

        $associationMock = Mockery::mock(Association::class);
        $associationMock->shouldReceive('create')->once()->with($requestData)->andReturn($fakeAssociation);

        $controller = new AssociationController($associationMock);

        $response = $controller->store($requestMock);

        $this->assertEquals(201, $response->status());
        $this->assertEquals(json_encode($fakeAssociation), $response->getContent());
    }

    /** @test */
    public function show_returns_association_with_entrepreneurs()
    {
        $fakeAssociation = (object)['id' => 5, 'name' => 'Test'];

        $associationMock = Mockery::mock(Association::class);
        $associationMock->shouldReceive('with')->once()->with('entrepreneurs')->andReturnSelf();
        $associationMock->shouldReceive('findOrFail')->once()->with(5)->andReturn($fakeAssociation);

        $controller = new AssociationController($associationMock);

        $response = $controller->show(5);

        $this->assertEquals(200, $response->status());
        $this->assertEquals(json_encode($fakeAssociation), $response->getContent());
    }

    /** @test */
    public function update_validates_and_updates_association()
    {
        $requestData = [
            'name' => 'Actualizado',
            'region' => 'Lima',
        ];

        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('validate')->once()->andReturn($requestData);

        $associationInstance = Mockery::mock();
        $associationInstance->shouldReceive('update')->once()->with($requestData);

        $associationMock = Mockery::mock(Association::class);
        $associationMock->shouldReceive('findOrFail')->once()->with(3)->andReturn($associationInstance);

        $controller = new AssociationController($associationMock);

        $response = $controller->update($requestMock, 3);

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function destroy_deletes_association_and_entrepreneurs()
    {
        $relationMock = Mockery::mock(HasMany::class);
        $relationMock->shouldReceive('delete')->once();

        $associationInstance = Mockery::mock();
        $associationInstance->shouldReceive('entrepreneurs')->once()->andReturn($relationMock);
        $associationInstance->shouldReceive('delete')->once();

        $associationMock = Mockery::mock(Association::class);
        $associationMock->shouldReceive('findOrFail')->once()->with(7)->andReturn($associationInstance);

        $controller = new AssociationController($associationMock);

        $response = $controller->destroy(7);

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function count_returns_the_total_number_of_associations()
    {
        $associationMock = Mockery::mock(Association::class);
        $associationMock->shouldReceive('count')->once()->andReturn(5);

        $controller = new AssociationController($associationMock);

        $response = $controller->count();

        $this->assertEquals(200, $response->status());
        $this->assertEquals(json_encode(['count' => 5]), $response->getContent());
    }
}
