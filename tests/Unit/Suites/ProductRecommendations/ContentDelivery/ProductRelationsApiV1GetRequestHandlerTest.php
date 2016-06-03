<?php

namespace LizardsAndPumpkins\ProductRecommendations\ContentDelivery;

use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\ProductRecommendations\Exception\UnableToProcessProductRelationsRequestException;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 */
class ProductRelationsApiV1GetRequestHandlerTest extends \PHPUnit_Framework_TestCase
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

    private $testNonMatchingRequstPaths = [
        '/api/products/test-sku',
        '/api/products/test-sku/relations/',
        '/api/products/relations/test-relation',
        '/api/products/test/sku/relations/test-relation',
    ];

    protected function setUp()
    {
        $this->mockProductRelationsService = $this->createMock(ProductRelationsService::class);

        $this->requestHandler = new ProductRelationsApiV1GetRequestHandler($this->mockProductRelationsService);
        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testItIsAnApiRequestHandler()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->requestHandler);
    }

    /**
     * @param string $nonGetRequestMethod
     * @dataProvider nonGetRequestMethodProvider
     */
    public function testItCanNotProcessNonHttpGetRequestTypes($nonGetRequestMethod)
    {
        $this->stubRequest->method('getMethod')->willReturn($nonGetRequestMethod);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);
        $message = sprintf('%s request should NOT be able to be processed', $nonGetRequestMethod);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    /**
     * @return array[]
     */
    public function nonGetRequestMethodProvider()
    {
        return [
            [HttpRequest::METHOD_POST],
            [HttpRequest::METHOD_PUT],
            [HttpRequest::METHOD_DELETE],
        ];
    }

    /**
     * @param string $nonMatchingRequestPath
     * @dataProvider nonMatchingRequestPathProvider
     */
    public function testItCanNotProcessNonMatchingGetRequests($nonMatchingRequestPath)
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn($nonMatchingRequestPath);
        $message = sprintf('GET request to "%s" should NOT be able to be processed', $nonMatchingRequestPath);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    /**
     * @return array[]
     */
    public function nonMatchingRequestPathProvider()
    {
        return array_map(function ($path) {
            return [$path];
        }, $this->testNonMatchingRequstPaths);
    }

    public function testItCanProcessMatchingGetRequests()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);
        $message = sprintf('Not able to process a GET request to "%s"', $this->testMatchingRequestPath);
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    public function testItThrowsAnExceptionIfANonProcessableRequestIsPassed()
    {
        $this->expectException(UnableToProcessProductRelationsRequestException::class);
        $this->expectExceptionMessage(
            sprintf('Unable to process a %s request to "%s"', HttpRequest::METHOD_POST, $this->testMatchingRequestPath)
        );

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);
        $this->requestHandler->process($this->stubRequest);
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
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);

        $response = $this->requestHandler->process($this->stubRequest);
        
        $this->assertSame(json_encode(['data' => $testProductData]), $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }
}
