<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 */
class ProductRelationsApiV1GetRequestHandlerTest extends TestCase
{
    /**
     * @var ProductRelationsApiV1GetRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var ProductRelationsService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductRelationsService;

    private $testMatchingRequestPath = '/api/products/test-sku/relations/test-relation';

    private $testNonMatchingRequestPaths = [
        '/api/products/test-sku',
        '/api/products/test-sku/relations/',
        '/api/products/relations/test-relation',
        '/api/products/test/sku/relations/test-relation',
    ];

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    /**
     * @var UrlToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlToWebsiteMap;

    protected function setUp()
    {
        $this->mockProductRelationsService = $this->createMock(ProductRelationsService::class);
        $this->stubContextBuilder = $this->createMock(ContextBuilder::class);
        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);

        $this->requestHandler = new ProductRelationsApiV1GetRequestHandler(
            $this->mockProductRelationsService,
            $this->stubUrlToWebsiteMap,
            $this->stubContextBuilder
        );

        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testItIsAnApiRequestHandler()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->requestHandler);
    }

    /**
     * @dataProvider nonGetRequestMethodProvider
     */
    public function testItCanNotProcessNonHttpGetRequestTypes(string $nonGetRequestMethod)
    {
        $this->stubRequest->method('getMethod')->willReturn($nonGetRequestMethod);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);
        $message = sprintf('%s request should NOT be able to be processed', $nonGetRequestMethod);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    /**
     * @return array[]
     */
    public function nonGetRequestMethodProvider() : array
    {
        return [
            [HttpRequest::METHOD_POST],
            [HttpRequest::METHOD_PUT],
            [HttpRequest::METHOD_DELETE],
        ];
    }

    /**
     * @dataProvider nonMatchingRequestPathProvider
     */
    public function testItCanNotProcessNonMatchingGetRequests(string $nonMatchingRequestPath)
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn($nonMatchingRequestPath);
        $message = sprintf('GET request to "%s" should NOT be able to be processed', $nonMatchingRequestPath);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    /**
     * @return array[]
     */
    public function nonMatchingRequestPathProvider() : array
    {
        return array_map(function ($path) {
            return [$path];
        }, $this->testNonMatchingRequestPaths);
    }

    public function testItCanProcessMatchingGetRequests()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);
        $message = sprintf('Not able to process a GET request to "%s"', $this->testMatchingRequestPath);
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    public function testItThrowsAnExceptionIfANonProcessableRequestIsPassed()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);

        $response = $this->requestHandler->process($this->stubRequest);
        $expectedResponseBody = json_encode([
            'error' => sprintf(
                'Unable to process a %s request to "%s"',
                HttpRequest::METHOD_POST,
                $this->testMatchingRequestPath
            )
        ]);

        $this->assertSame($expectedResponseBody, $response->getBody());
    }

    public function testItDelegatesToTheProductRelationsServiceToFetchRelatedProducts()
    {
        $testProductData = [
            ['Dummy Product Data'],
        ];
        $this->mockProductRelationsService->expects($this->once())
            ->method('getRelatedProductData')
            ->willReturn($testProductData);

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);

        $stubContext = $this->createMock(Context::class);

        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $response = $this->requestHandler->process($this->stubRequest);
        
        $this->assertSame(json_encode(['data' => $testProductData]), $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }
}
