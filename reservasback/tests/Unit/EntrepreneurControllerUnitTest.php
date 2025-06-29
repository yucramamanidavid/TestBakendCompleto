<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\EntrepreneurController;

class EntrepreneurControllerUnitTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_suspended_when_status_is_active()
    {
        $controller = new EntrepreneurController();

        $result = $this->invokeGetNextStatus($controller, 'activo');

        $this->assertEquals('suspendido', $result);
    }

    /**
     * @test
     */
    public function it_returns_active_when_status_is_suspended()
    {
        $controller = new EntrepreneurController();

        $result = $this->invokeGetNextStatus($controller, 'suspendido');

        $this->assertEquals('activo', $result);
    }

    /**
     * @test
     */
    public function it_throws_exception_for_invalid_status()
    {
        $controller = new EntrepreneurController();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Estado inválido: desconocido');

        $this->invokeGetNextStatus($controller, 'desconocido');
    }

    /**
     * Helper to call protected/private method
     */
    private function invokeGetNextStatus($controller, $status)
    {
        $method = new \ReflectionMethod($controller, 'getNextStatus');
        $method->setAccessible(true);

        return $method->invoke($controller, $status);
    }
}
// This test class is designed to test the getNextStatus method of the EntrepreneurController.