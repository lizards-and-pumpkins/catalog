<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\PageBuilder;
use Brera\SnippetKeyGenerator;
use Brera\SnippetKeyGeneratorLocator;

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
        /** @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject $stubDataPoolReader */
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        /** @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject $stubPageBuilder */
        $stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGeneratorLocator */
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($stubSnippetKeyGenerator);

        $this->builder = new ProductListingRequestHandlerBuilder(
            $stubDataPoolReader,
            $stubPageBuilder,
            $stubSnippetKeyGeneratorLocator
        );
    }

    public function testUrlKeyRequestHandlerIsCreated()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubUrl */
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $result = $this->builder->create($stubUrl, $stubContext);

        $this->assertInstanceOf(ProductListingRequestHandler::class, $result);
    }
}
