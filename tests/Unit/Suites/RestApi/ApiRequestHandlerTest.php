<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class ApiRequestHandlerTest extends TestCase
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
        $this->stubRequest = $this->createMock(HttpRequest::class);
        $this->apiRequestHandler = new StubApiRequestHandler;
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->apiRequestHandler);
    }

    public function testInstanceOfGenericHttpResponseIsReturned()
    {
        $result = $this->apiRequestHandler->process($this->stubRequest);
        $this->assertInstanceOf(GenericHttpResponse::class, $result);
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
    private function getPrivateFieldValue($object, string $field)
    {
        $property = new \ReflectionProperty($object, $field);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
