<?php

namespace Brera\Api;

use Brera\Api\Stubs\StubApiRequestHandler;
use Brera\DefaultHttpResponse;
use Brera\Http\HttpRequestHandler;

/**
 * @covers \Brera\Api\ApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 */
class ApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiRequestHandler
     */
    private $apiRequestHandler;

    protected function setUp()
    {
        $this->apiRequestHandler = new StubApiRequestHandler;
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->apiRequestHandler);
    }

    public function testInstanceOfDefaultHttpResponseIsReturned()
    {
        $result = $this->apiRequestHandler->process();
        $this->assertInstanceOf(DefaultHttpResponse::class, $result);
    }

    public function testApiSpecificHeadersAreSet()
    {
        $response = $this->apiRequestHandler->process();
        $result = $this->getPrivateFieldValue($response, 'headers');
        $expectedHeaders = [
            'Access-Control-Allow-Origin: *',
            'Access-Control-Allow-Methods: *',
            'Content-Type: application/json',
        ];

        $this->assertArraySubset($expectedHeaders, $result);
    }

    public function testDummyBodyContentIsReturned()
    {
        $response = $this->apiRequestHandler->process();
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
