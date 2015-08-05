<?php

namespace Brera\Product;

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
        $this->router = new ProductDetailViewRouter($this->mockRequestHandler);
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
