<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\Logger;
use Brera\PageBuilder;
use Brera\SnippetKeyGeneratorLocator;
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
    private $handlerBuilder;

    public function setUp()
    {
        $stubUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class, [], [], '', false);
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);
        
        $this->handlerBuilder = new ProductDetailViewRequestHandlerBuilder(
            $stubUrlPathKeyGenerator,
            $stubDataPoolReader,
            $stubPageBuilder
        );
    }

    /**
     * @test
     */
    public function itShouldCreateAnUrlKeyRequestHandler()
    {
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);

        $result = $this->handlerBuilder->create($stubUrl, $stubContext);

        $this->assertInstanceOf(ProductDetailViewRequestHandler::class, $result);
    }
}
