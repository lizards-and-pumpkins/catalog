<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\ProductRelations\Exception\UnableToProcessProductRelationsRequestException;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
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
        $this->stubRequest = $this->createMock(HttpRequest::class);

        $stubContext = $this->createMock(Context::class);

        $stubContextBuilder = $this->createMock(ContextBuilder::class);
        $stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $this->mockProductRelationsService = $this->createMock(ProductRelationsService::class);

        $stubProductRelationsServiceBuilder = $this->createMock(ProductRelationsServiceBuilder::class);
        $stubProductRelationsServiceBuilder->method('getForContext')->with($stubContext)
            ->willReturn($this->mockProductRelationsService);

        $this->requestHandler = new ProductRelationsApiV1GetRequestHandler(
            $stubProductRelationsServiceBuilder,
            $stubContextBuilder
        );
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
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn($this->testMatchingRequestPath);
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
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn($nonMatchingRequestPath);
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
