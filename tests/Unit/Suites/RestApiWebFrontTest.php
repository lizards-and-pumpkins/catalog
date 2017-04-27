<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchFactory;
use LizardsAndPumpkins\RestApi\ApiRouter;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApiWebFront
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\WebFront
 */
class RestApiWebFrontTest extends TestCase
{
    /**
     * @var RestApiWebFront
     */
    private $webFront;

    /**
     * @var HttpRouterChain|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRouterChain;

    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockMasterFactory;

    /**
     * @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpResponse;

    private function initMasterFactoryMock(HttpRequestHandler $httpRequestHandler)
    {
        $stubFactoryMethods = array_merge(
            get_class_methods(MasterFactory::class),
            ['getContext', 'createHttpRouterChain', 'createApiRouter']
        );

        $this->mockRouterChain = $this->createMock(HttpRouterChain::class);
        $this->mockRouterChain->method('route')->willReturn($httpRequestHandler);

        $this->mockMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods($stubFactoryMethods)->getMock();
        $this->mockMasterFactory->method('getContext')->willReturn($this->createMock(Context::class));
        $this->mockMasterFactory->method('createHttpRouterChain')->willReturn($this->mockRouterChain);
        $this->mockMasterFactory->method('createApiRouter')->willReturn($this->createMock(ApiRouter::class));
    }

    final protected function setUp()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);

        $this->stubHttpResponse = $this->createMock(HttpResponse::class);
        $this->stubHttpResponse->method('getStatusCode')->willReturn(HttpResponse::STATUS_OK);

        /** @var HttpRequestHandler|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequestHandler */
        $stubHttpRequestHandler = $this->createMock(HttpRequestHandler::class);
        $stubHttpRequestHandler->method('process')->willReturn($this->stubHttpResponse);

        $this->initMasterFactoryMock($stubHttpRequestHandler);

        $this->webFront = new TestRestApiWebFront(
            $stubHttpRequest,
            $this->mockMasterFactory,
            new UnitTestFactory($this)
        );
    }

    public function testIsWebFront()
    {
        $this->assertInstanceOf(WebFront::class, $this->webFront);
    }

    public function testReturnsHttpResponse()
    {
        $this->assertInstanceOf(HttpResponse::class, $this->webFront->processRequest());
    }

    public function testCorsHeadersAreAdded()
    {
        $originalHeaders = ['Foo' => 'Bar'];
        $stubHttpHeaders = HttpHeaders::fromArray($originalHeaders);
        $this->stubHttpResponse->method('getHeaders')->willReturn($stubHttpHeaders);

        $response = $this->webFront->processRequest();

        $expectedHeaders = array_merge($originalHeaders, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => '*',
            'Content-Type' => 'application/json',
        ]);

        $this->assertEquals($expectedHeaders, $response->getHeaders()->getAll());
    }

    public function testReturnsJsonErrorResponseInCaseOfExceptions()
    {
        $exceptionMessage = 'foo';

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);

        /** @var HttpRequestHandler|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequestHandler */
        $stubHttpRequestHandler = $this->createMock(HttpRequestHandler::class);
        $stubHttpRequestHandler->method('process')->willThrowException(new \Exception($exceptionMessage));

        $this->initMasterFactoryMock($stubHttpRequestHandler);

        $webFront = new TestRestApiWebFront($stubHttpRequest, $this->mockMasterFactory, new UnitTestFactory($this));
        $response = $webFront->processRequest();

        $this->assertSame(HttpResponse::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(json_encode(['error' => $exceptionMessage]), $response->getBody());
    }

    public function testRegistersApiRouter()
    {
        $this->mockRouterChain->expects($this->once())->method('register')->with($this->isInstanceOf(ApiRouter::class));
        $this->webFront->processRequest();
    }

    public function testRegistersFactoriesRequiredForRestApiRequestHandling()
    {
        $this->mockMasterFactory->expects($this->exactly(6))->method('register')->withConsecutive(
            $this->isInstanceOf(RestApiFactory::class),
            $this->isInstanceOf(ProductSearchFactory::class),
            $this->isInstanceOf(UpdatingProductImportCommandFactory::class),
            $this->isInstanceOf(UpdatingProductImageImportCommandFactory::class),
            $this->isInstanceOf(UpdatingProductListingImportCommandFactory::class),
            $this->isInstanceOf(UnitTestFactory::class)
        );

        $this->webFront->processRequest();
    }
}
