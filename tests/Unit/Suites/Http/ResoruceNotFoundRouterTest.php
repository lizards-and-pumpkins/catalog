<?php


namespace Brera\Http;

use Brera\Context\Context;

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
        $stubContext = $this->getMock(Context::class);
        $result = $this->router->route($stubRequest, $stubContext);
        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }
}
