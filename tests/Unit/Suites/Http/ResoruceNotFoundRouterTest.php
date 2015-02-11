<?php


namespace Brera\Http;

use Brera\Environment\Environment;

/**
 * @covers \Brera\Http\ResourceNotFoundRouter
 */
class ResoruceNotFoundRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceNotFoundRouter
     */
    private $router;

    public function setUp()
    {
        $this->router = new ResourceNotFoundRouter();
    }

    /**
     * @test
     */
    public function itShouldReturnAResourceNotFoundRequestHandler()
    {
        $stubRequest = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()->getMock();
        $stubEnvironment = $this->getMock(Environment::class);
        $result = $this->router->route($stubRequest, $stubEnvironment);
        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }
}
