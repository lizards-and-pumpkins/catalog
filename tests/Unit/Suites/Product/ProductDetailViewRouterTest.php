<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductDetailViewRouter
 * @uses \Brera\Http\HttpUrl
 */
class ProductDetailViewRouterTest extends AbstractRouterTest
{
    public function setUp()
    {
        $this->mockRequestHandler = $this->getMock(ProductDetailViewRequestHandler::class, [], [], '', false);

        $this->mockRequestHandlerBuilder = $this->getMock(ProductDetailViewRequestHandlerBuilder::class, [], [], '', false);
        $this->mockRequestHandlerBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->mockRequestHandler);

        $this->router = new ProductDetailViewRouter($this->mockRequestHandlerBuilder);
    }
}
