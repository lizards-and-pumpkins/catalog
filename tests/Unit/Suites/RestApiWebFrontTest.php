<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiFactory;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\RestApi\RestApiRequestHandler;
use LizardsAndPumpkins\RestApi\RestApiRequestHandlerLocator;
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
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockMasterFactory;

    /**
     * @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpResponse;

    private function initMasterFactoryMock(RestApiRequestHandler $requestHandler)
    {
        $stubFactoryMethods = array_merge(
            get_class_methods(MasterFactory::class),
            ['getContext', 'createHttpRouterChain', 'createApiRouter', 'getRestApiRequestHandlerLocator']
        );

        $this->mockMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods($stubFactoryMethods)->getMock();
        $this->mockMasterFactory->method('getContext')->willReturn($this->createMock(Context::class));

        $stubRestApiRequestHandlerLocator = $this->createMock(RestApiRequestHandlerLocator::class);
        $stubRestApiRequestHandlerLocator->method('getApiRequestHandler')->willReturn($requestHandler);

        $this->mockMasterFactory->method('getRestApiRequestHandlerLocator')
            ->willReturn($stubRestApiRequestHandlerLocator);
    }

    final protected function setUp()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);

        $this->stubHttpResponse = $this->createMock(HttpResponse::class);
        $this->stubHttpResponse->method('getStatusCode')->willReturn(HttpResponse::STATUS_OK);

        /** @var RestApiRequestHandler|\PHPUnit_Framework_MockObject_MockObject $stubRequestHandler */
        $stubRequestHandler = $this->createMock(RestApiRequestHandler::class);
        $stubRequestHandler->method('process')->willReturn($this->stubHttpResponse);

        $this->initMasterFactoryMock($stubRequestHandler);

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

        /** @var RestApiRequestHandler|\PHPUnit_Framework_MockObject_MockObject $stubRequestHandler */
        $stubRequestHandler = $this->createMock(RestApiRequestHandler::class);
        $stubRequestHandler->method('process')->willThrowException(new \Exception($exceptionMessage));

        $this->initMasterFactoryMock($stubRequestHandler);

        $webFront = new TestRestApiWebFront($stubHttpRequest, $this->mockMasterFactory, new UnitTestFactory($this));
        $response = $webFront->processRequest();

        $this->assertSame(HttpResponse::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(json_encode(['error' => $exceptionMessage]), $response->getBody());
    }

    public function testRegistersFactoriesRequiredForRestApiRequestHandling()
    {
        $this->mockMasterFactory->expects($this->exactly(6))->method('register')->withConsecutive(
            $this->isInstanceOf(RestApiFactory::class),
            $this->isInstanceOf(ProductSearchApiFactory::class),
            $this->isInstanceOf(UpdatingProductImportCommandFactory::class),
            $this->isInstanceOf(UpdatingProductImageImportCommandFactory::class),
            $this->isInstanceOf(UpdatingProductListingImportCommandFactory::class),
            $this->isInstanceOf(UnitTestFactory::class)
        );

        $this->webFront->processRequest();
    }
}
