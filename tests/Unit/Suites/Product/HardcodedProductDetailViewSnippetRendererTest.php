<?php

namespace Brera\PoC\Product;

use Brera\PoC\SnippetResultList;
use Brera\PoC\ProjectionSourceData;
use Brera\PoC\SnippetRenderer;
use Brera\PoC\VersionedEnvironment;
use Brera\PoC\SnippetResult;

class HardcodedProductDetailViewSnippetRendererTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HardcodedProductDetailViewSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var HardcodedProductDetailViewSnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockKeyGenerator;

    public function setUp()
    {
        $this->mockKeyGenerator
            = $this->getMock(HardcodedProductDetailViewSnippetKeyGenerator::class,
            array('getKey'));

        $this->mockKeyGenerator->expects($this->any())->method('getKey')
            ->willReturn('test');

        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);

        $this->snippetRenderer = new HardcodedProductDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $this->mockKeyGenerator
        );
    }

    /**
     * @test
     */
    public function itShouldBeASnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    /**
     * @test
     *
     * @expectedException \Brera\PoC\Product\InvalidArgumentException
     */
    public function itShouldOnlyAcceptProductsForRendering()
    {
        $invalidSourceObject = $this->getMockBuilder(ProjectionSourceData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubEnvironment = $this->getMockBuilder(VersionedEnvironment::class)
            ->disableOriginalConstructor()->getMock();

        $this->snippetRenderer->render($invalidSourceObject, $stubEnvironment);
    }

    /**
     * @test
     */
    public function itShouldReturnASnippetResultList()
    {
        $stubProduct = $this->getStubProduct();
        
        $stubEnvironment = $this->getMockBuilder(VersionedEnvironment::class)
            ->disableOriginalConstructor()->getMock();

        $result = $this->snippetRenderer->render(
            $stubProduct, $stubEnvironment
        );
        $this->assertSame($this->mockSnippetResultList, $result);
    }

    /**
     * @test
     */
    public function itShouldAddOneOrMoreSnippetToTheSnippetList()
    {
        $stubProduct = $this->getStubProduct();
        
        $stubEnvironment = $this->getMockBuilder(VersionedEnvironment::class)
            ->disableOriginalConstructor()->getMock();

        $this->mockSnippetResultList->expects($this->atLeastOnce())
            ->method('add')->with($this->isInstanceOf(SnippetResult::class));

        $this->snippetRenderer->render($stubProduct, $stubEnvironment);
    }

    /**
     * @test
     */
    public function itShouldRenderAProductDetailView()
    {
        $productIdString = 'test-123';
        $productNameString = 'Test Name';
        $stubProduct = $this->getStubProduct();
        $stubProduct->getId()->expects($this->any())
            ->method('getId')->willReturn($productIdString);
        $stubProduct->getId()->expects($this->any())
            ->method('__toString')->willReturn($productIdString);
        $stubProduct->expects($this->any())
            ->method('getAttributeValue')
	        ->with('name')
	        ->willReturn($productNameString);

        $stubEnvironment = $this->getMockBuilder(VersionedEnvironment::class)
            ->disableOriginalConstructor()->getMock();

        $transport = '';
        $this->mockSnippetResultList->expects($this->atLeastOnce())->method('add')
            ->willReturnCallback(function($snippetResult) use (&$transport) {
                $transport = $snippetResult;
            });

        $this->snippetRenderer->render($stubProduct, $stubEnvironment);
        
        /** @var $transport SnippetResult */
        $expected = "<div>$productNameString ($productIdString)</div>";
        $this->assertEquals($expected, $transport->getContent());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Product
     */
    private function getStubProduct()
    {
        $stubProductId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()->getMock();
        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()->getMock();
        $stubProduct->expects($this->any())->method('getId')
            ->willReturn($stubProductId);
        return $stubProduct;
    }
}
