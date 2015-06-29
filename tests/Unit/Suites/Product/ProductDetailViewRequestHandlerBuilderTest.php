<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\PageBuilder;
use Brera\UrlPathKeyGenerator;

/**
 * @covers \Brera\Product\ProductDetailViewRequestHandlerBuilder
 * @uses   \Brera\Product\ProductDetailViewRequestHandler
 */
class ProductDetailViewRequestHandlerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductDetailViewRequestHandlerBuilder
     */
    private $builder;

    public function setUp()
    {
        $stubUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class, [], [], '', false);
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);
        
        $this->builder = new ProductDetailViewRequestHandlerBuilder(
            $stubUrlPathKeyGenerator,
            $stubDataPoolReader,
            $stubPageBuilder
        );
    }

    public function testUrlKeyRequestHandlerIsCreated()
    {
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);

        $result = $this->builder->create($stubUrl, $stubContext);

        $this->assertInstanceOf(ProductDetailViewRequestHandler::class, $result);
    }
}
