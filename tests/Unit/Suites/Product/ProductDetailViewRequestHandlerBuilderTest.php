<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\PageBuilder;
use Brera\SnippetKeyGenerator;

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
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject $stubDataPoolReader */
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        /** @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject $stubPageBuilder */
        $stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);
        
        $this->builder = new ProductDetailViewRequestHandlerBuilder(
            $stubSnippetKeyGenerator,
            $stubDataPoolReader,
            $stubPageBuilder
        );
    }

    public function testUrlKeyRequestHandlerIsCreated()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubUrl */
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $result = $this->builder->create($stubUrl, $stubContext);

        $this->assertInstanceOf(ProductDetailViewRequestHandler::class, $result);
    }
}
