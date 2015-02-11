<?php


namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpResourceNotFoundResponse
 */
class HttpResourceNotFoundResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpResourceNotFoundResponse
     */
    private $response;

    public function setUp()
    {
        $this->response = new HttpResourceNotFoundResponse();
    }

    /**
     * @test
     */
    public function itShouldSetA404ResponseCode()
    {
        ob_start();
        $this->response->send();
        ob_end_clean();
        $this->assertEquals(404, http_response_code());
    }
}
