<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ProductJsonSnippetRendererTest extends TestCase
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
        $stubProductJsonKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubProductJsonKeyGenerator->method('getKeyForContext')->willReturn('test-key');

        $this->snippetRenderer = new ProductJsonSnippetRenderer($stubProductJsonKeyGenerator);

        $this->stubProductView = $this->createMock(ProductView::class);
        $this->stubProductView->method('getContext')->willReturn($this->createMock(Context::class));
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
