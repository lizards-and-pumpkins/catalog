<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
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
    private $renderer;

    /**
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductView;

    final protected function setUp()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubProductJsonKeyGenerator */
        $stubProductJsonKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubProductJsonKeyGenerator->method('getKeyForContext')->willReturn('test-key');

        $this->renderer = new ProductJsonSnippetRenderer($stubProductJsonKeyGenerator);

        $this->stubProductView = $this->createMock(ProductView::class);
        $this->stubProductView->method('getContext')->willReturn($this->createMock(Context::class));
    }

    public function testThrowsExceptionIfDataObjectIsNotProductView()
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be ProductView, got string.');

        $this->renderer->render('foo');
    }

    public function testIsSnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testReturnsJsonSerializedProduct()
    {
        $expectedSnippetContent = ['product_id' => 'test-dummy'];

        $this->stubProductView->method('jsonSerialize')->willReturn($expectedSnippetContent);

        $result = $this->renderer->render($this->stubProductView);

        $this->assertCount(1, $result);
        $this->assertEquals(json_encode($expectedSnippetContent), $result[0]->getContent());
    }
}
