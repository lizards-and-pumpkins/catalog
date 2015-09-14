<?php

namespace LizardsAndPumpkins\Api;

use LizardsAndPumpkins\Api\Stubs\StubApiRequestHandler;
use LizardsAndPumpkins\DefaultHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;

/**
 * @covers \LizardsAndPumpkins\Api\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\DefaultHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class ApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var ApiRequestHandler
     */
    private $apiRequestHandler;

    protected function setUp()
    {
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->apiRequestHandler = new StubApiRequestHandler;
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->apiRequestHandler);
    }

    public function testInstanceOfDefaultHttpResponseIsReturned()
    {
        $result = $this->apiRequestHandler->process($this->stubRequest);
        $this->assertInstanceOf(DefaultHttpResponse::class, $result);
    }

    public function testApiSpecificHeadersAreSet()
    {
        $response = $this->apiRequestHandler->process($this->stubRequest);
        $headers = $this->getPrivateFieldValue($response, 'headers');
        $expectedHeaders = [
            'access-control-allow-origin' => '*',
            'access-control-allow-methods' => '*',
            'content-type' => 'application/json',
        ];

        $this->assertArraySubset($expectedHeaders, $headers->getAll());
    }

    public function testDummyBodyContentIsReturned()
    {
        $response = $this->apiRequestHandler->process($this->stubRequest);
        $result = $response->getBody();
        $expectedBodyContent = StubApiRequestHandler::DUMMY_BODY_CONTENT;

        $this->assertSame($expectedBodyContent, $result);
    }

    /**
     * @param mixed $object
     * @param string $field
     * @return mixed
     */
    private function getPrivateFieldValue($object, $field)
    {
        $property = new \ReflectionProperty($object, $field);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
