<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductJsonSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductJsonSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductView;

    protected function setUp()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubProductJsonKeyGenerator */
        $stubProductJsonKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubProductJsonKeyGenerator->method('getKeyForContext')->willReturn('test-key');

        /** @var InternalToPublicProductJsonData|\PHPUnit_Framework_MockObject_MockObject $stubInternalToPublicJson */
        $stubInternalToPublicJson = $this->getMock(InternalToPublicProductJsonData::class);
        $stubInternalToPublicJson->method('transformProduct')->willReturnArgument(0);
        
        $this->snippetRenderer = new ProductJsonSnippetRenderer(
            $stubProductJsonKeyGenerator,
            $stubInternalToPublicJson
        );
        
        $this->stubProductView = $this->getMock(ProductView::class);
        $this->stubProductView->method('getContext')->willReturn($this->getMock(Context::class));
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testItReturnsJsonSerializedProduct()
    {
        $expectedSnippetContent = ['product_id' => 'test-dummy'];
        
        $this->stubProductView->method('jsonSerialize')->willReturn($expectedSnippetContent);
        
        $result = $this->snippetRenderer->render($this->stubProductView);
        
        $this->assertCount(1, $result);
        $this->assertEquals(json_encode($expectedSnippetContent), $result[0]->getContent());
    }
}
