<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingRouter
 * @uses   \Brera\Http\HttpUrl
 */
class ProductListingRouterTest extends AbstractRouterTest
{
    /**
     * @var ProductListingRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestHandler;

    /**
     * @var ProductListingRouter
     */
    private $router;

    public function setUp()
    {
        $this->mockRequestHandler = $this->getMock(ProductListingRequestHandler::class, [], [], '', false);

        $mockRequestHandlerBuilder = $this->getMock(ProductListingRequestHandlerBuilder::class, [], [], '', false);
        $mockRequestHandlerBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->mockRequestHandler);

        $this->router = new ProductListingRouter($mockRequestHandlerBuilder);
    }

    /**
     * @return ProductListingRouter
     */
    protected function getRouterUnderTest()
    {
        return $this->router;
    }

    /**
     * @return ProductListingRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRequestHandler()
    {
        return $this->mockRequestHandler;
    }
}
