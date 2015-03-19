<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;

/**
 * @covers \Brera\ProductDetailViewRequestHandlerBuilder
 * @uses \Brera\ProductDetailViewRequestHandler
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
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $stubLogger = $this->getMock(Logger::class);

        $this->builder = new ProductDetailViewRequestHandlerBuilder(
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

        $this->assertInstanceOf(ProductDetailViewRequestHandler::class, $result);
    }
}
