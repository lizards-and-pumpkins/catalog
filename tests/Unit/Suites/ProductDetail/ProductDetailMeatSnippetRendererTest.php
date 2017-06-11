<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductDetail\TemplateRendering\ProductDetailViewBlockRenderer;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class ProductDetailMeatSnippetRendererTest extends TestCase
{
    /**
     * @var ProductDetailMetaSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductView;

    /**
     * @return ProductDetailViewBlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductDetailViewBlockRenderer(): ProductDetailViewBlockRenderer
    {
        $blockRenderer = $this->createMock(ProductDetailViewBlockRenderer::class);
        $blockRenderer->method('render')->willReturnCallback(function () {
            return '';
        });
        $blockRenderer->method('getRootSnippetCode')->willReturn(new SnippetCode('dummy root block code'));
        $blockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        return $blockRenderer;
    }

    final protected function setUp()
    {
        $blockRenderer = $this->createStubProductDetailViewBlockRenderer();
        $this->stubProductDetailViewSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);

        $this->renderer = new ProductDetailMetaSnippetRenderer($blockRenderer, $this->stubSnippetKeyGenerator);

        $this->stubProductView = $this->createMock(ProductView::class);
        $this->stubProductView->method('getContext')->willReturn($this->createMock(Context::class));
    }

    public function testIsSnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testThrowsExceptionIfDataObjectIsNotProductView()
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be ProductView, got string.');

        $this->renderer->render('foo');
    }

    public function testRendersProductDetailViewSnippets()
    {
        $testMetaSnippetKey = 'stub-meta-key';

        $this->stubSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($testMetaSnippetKey);

        $this->stubProductView->method('getAllValuesOfAttribute')->willReturn([]);
        $result = $this->renderer->render($this->stubProductView);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Snippet::class, $result[0]);
        $this->assertSame($testMetaSnippetKey, $result[0]->getKey());
    }
}
