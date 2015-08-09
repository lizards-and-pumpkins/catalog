<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductSearchResultsRouter
 * @uses   \Brera\Http\HttpUrl
 */
class ProductSearchResultsRouterTest extends AbstractRouterTest
{
    /**
     * @var ProductSearchRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestHandler;

    /**
     * @var ProductSearchResultsRouter
     */
    private $router;

    public function setUp()
    {
        $this->mockRequestHandler = $this->getMock(ProductSearchRequestHandler::class, [], [], '', false);
        $this->router = new ProductSearchResultsRouter($this->mockRequestHandler);
    }

    /**
     * @return ProductSearchResultsRouter
     */
    protected function getRouterUnderTest()
    {
        return $this->router;
    }

    /**
     * @return ProductSearchRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRequestHandler()
    {
        return $this->mockRequestHandler;
    }
}
