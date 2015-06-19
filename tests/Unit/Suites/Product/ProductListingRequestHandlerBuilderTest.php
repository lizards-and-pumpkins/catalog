<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\PageBuilder;
use Brera\SnippetKeyGeneratorLocator;
use Brera\UrlPathKeyGenerator;

/**
 * @covers \Brera\Product\ProductListingRequestHandlerBuilder
 * @uses   \Brera\Product\ProductListingRequestHandler
 */
class ProductListingRequestHandlerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingRequestHandlerBuilder
     */
    private $builder;

    public function setUp()
    {
        $stubUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class, [], [], '', false);
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);

        $this->builder = new ProductListingRequestHandlerBuilder(
            $stubUrlPathKeyGenerator,
            $stubDataPoolReader,
            $stubPageBuilder,
            $stubSnippetKeyGeneratorLocator
        );
    }

    public function testUrlKeyRequestHandlerIsCreated()
    {
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);

        $result = $this->builder->create($stubUrl, $stubContext);

        $this->assertInstanceOf(ProductListingRequestHandler::class, $result);
    }
}
