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
        $this->router = new ProductListingRouter($this->mockRequestHandler);
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
