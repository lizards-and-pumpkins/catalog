<?php


namespace Brera\Http;

use Brera\Environment\Environment;

/**
 * @covers \Brera\Http\Default404Router
 */
class Default404RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Default404Router
     */
    private $router;

    public function setUp()
    {
        $this->router = new Default404Router();
    }

    /**
     * @test
     */
    public function itShouldAlwaysReturnA404RequestHandler()
    {
        $stubRequest = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()->getMock();
        $stubEnvironment = $this->getMock(Environment::class);
        $result = $this->router->route($stubRequest, $stubEnvironment);
        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }
}
