<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingRouter
 * @uses \Brera\Http\HttpUrl
 */
class ProductListingRouterTest extends AbstractRouterTest
{
    public function setUp()
    {
        $this->mockRequestHandler = $this->getMock(ProductListingRequestHandler::class, [], [], '', false);

        $this->mockRequestHandlerBuilder = $this->getMock(ProductListingRequestHandlerBuilder::class, [], [], '', false);
        $this->mockRequestHandlerBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->mockRequestHandler);

        $this->router = new ProductListingRouter($this->mockRequestHandlerBuilder);
    }
}
