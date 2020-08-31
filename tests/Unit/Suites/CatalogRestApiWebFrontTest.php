<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockServiceFactory;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiFactory;
use LizardsAndPumpkins\RestApi\ApiRouter;
use LizardsAndPumpkins\RestApi\CatalogRestApiFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\CatalogRestApiWebFront
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\WebFront
 */
class CatalogRestApiWebFrontTest extends TestCase
{
    /**
     * @var CatalogRestApiWebFront
     */
    private $webFront;

    /**
     * @var HttpRouterChain|MockObject
     */
    private $mockRouterChain;

    /**
     * @var MasterFactory|MockObject
     */
    private $mockMasterFactory;

    /**
     * @var HttpResponse|MockObject
     */
    private $stubHttpResponse;

    private function initMasterFactoryMock(HttpRequestHandler $httpRequestHandler): void
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

    final protected function setUp(): void
    {
        $stubHttpRequest = $this->createMock(HttpRequest::class);

        $this->stubHttpResponse = $this->createMock(HttpResponse::class);
        $this->stubHttpResponse->method('getStatusCode')->willReturn(HttpResponse::STATUS_OK);

        /** @var HttpRequestHandler|MockObject $stubHttpRequestHandler */
        $stubHttpRequestHandler = $this->createMock(HttpRequestHandler::class);
        $stubHttpRequestHandler->method('process')->willReturn($this->stubHttpResponse);

        $this->initMasterFactoryMock($stubHttpRequestHandler);

        $this->webFront = new TestRestApiWebFront(
            $stubHttpRequest,
            $this->mockMasterFactory,
            new UnitTestFactory($this)
        );
    }

    public function testIsWebFront(): void
    {
        $this->assertInstanceOf(WebFront::class, $this->webFront);
    }

    public function testReturnsHttpResponse(): void
    {
        $this->assertInstanceOf(HttpResponse::class, $this->webFront->processRequest());
    }

    public function testCorsHeadersAreAdded(): void
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

    public function testReturnsJsonErrorResponseInCaseOfExceptions(): void
    {
        $exceptionMessage = 'foo';

        $stubHttpRequest = $this->createMock(HttpRequest::class);

        /** @var HttpRequestHandler|MockObject $stubHttpRequestHandler */
        $stubHttpRequestHandler = $this->createMock(HttpRequestHandler::class);
        $stubHttpRequestHandler->method('process')->willThrowException(new \Exception($exceptionMessage));

        $this->initMasterFactoryMock($stubHttpRequestHandler);

        $webFront = new TestRestApiWebFront($stubHttpRequest, $this->mockMasterFactory, new UnitTestFactory($this));
        $response = $webFront->processRequest();

        $this->assertSame(HttpResponse::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(json_encode(['error' => $exceptionMessage]), $response->getBody());
    }

    public function testRegistersApiRouter(): void
    {
        $this->mockRouterChain->expects($this->once())->method('register')->with($this->isInstanceOf(ApiRouter::class));
        $this->webFront->processRequest();
    }

    public function testRegistersFactoriesRequiredForRestApiRequestHandling(): void
    {
        $expectedFactories = [
            CommonFactory::class,
            CatalogRestApiFactory::class,
            ContentBlockServiceFactory::class,
            ProductSearchApiFactory::class,
            UpdatingProductImportCommandFactory::class,
            UpdatingProductImageImportCommandFactory::class,
            UpdatingProductListingImportCommandFactory::class,
        ];

        $this->mockMasterFactory->expects($this->atLeast(7))->method('register')
            ->willReturnCallback(function (Factory $factory) use ($expectedFactories) {
                $this->assertContains(get_class($factory), $expectedFactories);
            });

        $this->webFront->processRequest();
    }
}
