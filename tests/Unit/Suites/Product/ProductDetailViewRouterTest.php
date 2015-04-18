<?php

namespace Brera\Product;

use Brera\Http\HttpRouter;

/**
 * @covers \Brera\Product\ProductDetailViewRouter
 * @uses   \Brera\Http\HttpUrl
 */
class ProductDetailViewRouterTest extends AbstractRouterTest
{
    /**
     * @var ProductDetailViewRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestHandler;

    /**
     * @var ProductDetailViewRouter
     */
    private $router;

    public function setUp()
    {
        $this->mockRequestHandler = $this->getMock(ProductDetailViewRequestHandler::class, [], [], '', false);

        $mockRequestHandlerBuilder = $this->getMock(ProductDetailViewRequestHandlerBuilder::class, [], [], '', false);
        $mockRequestHandlerBuilder->method('create')
            ->willReturn($this->mockRequestHandler);

        $this->router = new ProductDetailViewRouter($mockRequestHandlerBuilder);
    }

    /**
     * @return ProductDetailViewRouter
     */
    protected function getRouterUnderTest()
    {
        return $this->router;
    }

    /**
     * @return ProductDetailViewRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRequestHandler()
    {
        return $this->mockRequestHandler;
    }
}
