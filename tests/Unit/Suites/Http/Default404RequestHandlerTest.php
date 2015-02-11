<?php


namespace Brera\Http;

/**
 * @covers \Brera\Http\Default404RequestHandler
 */
class Default404RequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Default404RequestHandler
     */
    private $requestHandler;

    public function setUp()
    {
        $this->requestHandler = new Default404RequestHandler();
    }

    /**
     * @test
     */
    public function itShouldReturnAHttpResourceNotFoundResponse()
    {
        $result = $this->requestHandler->process();
        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $result);
    }
}
