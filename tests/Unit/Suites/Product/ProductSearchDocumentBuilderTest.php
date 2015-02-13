<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\Environment\EnvironmentSource;
use Brera\KeyValue\SearchDocumentBuilder;
use Brera\SearchEngine\SearchEngine;

/**
 * @covers \Brera\Product\ProductSearchDocumentBuilder
 */
class ProductSearchDocumentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchEngine;

    /**
     * @var ProductSearchDocumentBuilder
     */
    private $searchIndexer;

    protected function setUp()
    {
        $this->stubSearchEngine = $this->getMock(SearchEngine::class);

        $this->searchIndexer = new ProductSearchDocumentBuilder($this->stubSearchEngine, ['name']);
    }

    /**
     * @test
     */
    public function itShouldImplementSearchIndexer()
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, $this->searchIndexer);
    }

    /**
     * @test
     */
    public function itShouldAddEntryWithProductIdAndNameToSearchIndex()
    {
        $stubEnvironment = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubEnvironment->expects($this->atLeastOnce())
            ->method('getSupportedCodes')
            ->willReturn(['version']);
        $stubEnvironment->expects($this->atLeastOnce())
            ->method('getValue')
            ->with('version')
            ->willReturn(-1);

        $stubEnvironmentSource = $this->getMockBuilder(EnvironmentSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubEnvironmentSource->expects($this->atLeastOnce())
            ->method('extractEnvironments')
            ->willReturn([$stubEnvironment]);

        $stubProductId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProductId->expects($this->atLeastOnce())
            ->method('__toString')
            ->willReturn('foo');

        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProduct->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($stubProductId);
        $stubProduct->expects($this->atLeastOnce())
            ->method('getAttributeValue')
            ->with('name')
            ->willReturn('bar');

        $stubProductSource = $this->getMockBuilder(ProductSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProductSource->expects($this->atLeastOnce())
            ->method('getProductForEnvironment')
            ->with($stubEnvironment)
            ->willReturn($stubProduct);

        $this->stubSearchEngine->expects($this->once())
            ->method('addMultiToIndex')
            ->with([[
                'product_id'    => 'foo',
                'name'          => 'bar',
                'version'       => -1
            ]]);

        $this->searchIndexer->aggregate($stubProductSource, $stubEnvironmentSource);
    }
}
