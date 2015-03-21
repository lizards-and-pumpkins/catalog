<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\Logger;
use Brera\SnippetKeyGeneratorLocator;
use Brera\UrlPathKeyGenerator;

/**
 * @covers \Brera\Product\ProductListingRequestHandlerBuilder
 * @uses \Brera\Product\ProductListingRequestHandler
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
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $stubLogger = $this->getMock(Logger::class);

        $this->builder = new ProductListingRequestHandlerBuilder(
            $stubUrlPathKeyGenerator,
            $stubSnippetKeyGeneratorLocator,
            $stubDataPoolReader,
            $stubLogger
        );
    }

    /**
     * @test
     */
    public function itShouldCreateAnUrlKeyRequestHandler()
    {
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);

        $result = $this->builder->create($stubUrl, $stubContext);

        $this->assertInstanceOf(ProductListingRequestHandler::class, $result);
    }
}
