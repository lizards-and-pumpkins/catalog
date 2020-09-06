<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\View\CompositeProductView;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ConfigurableProductJsonSnippetRendererTest extends TestCase
{
    private $testVariationAttributesSnippetKey = 'variations';
    private $testAssociatedProductsSnippetKey = 'associated_products';
    private $testVariationAttributesJsonData = ['variations'];
    private $testAssociatedAttributesJsonData = ['children'];

    /**
     * @var ConfigurableProductJsonSnippetRenderer
     */
    private $renderer;

    /**
     * @var CompositeProductView|MockObject
     */
    private $stubCompositeProductView;

    private function getSnippetWithKey(string $snippetKey, Snippet ...$snippets) : Snippet
    {
        foreach ($snippets as $snippet) {
            if ($snippet->getKey() === $snippetKey) {
                return $snippet;
            }
        }

        $this->fail(sprintf('No snippet with key "%s" found in snippet list', $snippetKey));
    }

    private function assertSnippetContent(string $expected, Snippet $snippet): void
    {
        $this->assertSame($expected, $snippet->getContent());
    }

    final protected function setUp(): void
    {
        $stubVariationAttributesJsonSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubVariationAttributesJsonSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testVariationAttributesSnippetKey);

        $stubAssociatedProductsJsonSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubAssociatedProductsJsonSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testAssociatedProductsSnippetKey);
        
        $this->renderer = new ConfigurableProductJsonSnippetRenderer(
            $stubVariationAttributesJsonSnippetKeyGenerator,
            $stubAssociatedProductsJsonSnippetKeyGenerator
        );

        $this->stubCompositeProductView = $this->createMock(CompositeProductView::class);
        $this->stubCompositeProductView->method('getContext')->willReturn($this->createMock(Context::class));

        $this->stubCompositeProductView->method('getAssociatedProducts')
            ->willReturn($this->testAssociatedAttributesJsonData);

        $stubVariationAttributes = $this->createMock(ProductVariationAttributeList::class);
        $stubVariationAttributes->method('jsonSerialize')->willReturn($this->testVariationAttributesJsonData);
        $this->stubCompositeProductView->method('getVariationAttributes')->willReturn($stubVariationAttributes);
    }

    public function testThrowsExceptionIfDataObjectIsNotProductView(): void
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be ProductView, got string.');

        $this->renderer->render('foo');
    }

    public function testReturnsAnEmptyVariationAttributesJsonArraySnippetForNonCompositeProducts(): void
    {
        /** @var ProductView|MockObject $stubNonCompositeProduct */
        $stubNonCompositeProduct = $this->createMock(ProductView::class);
        $stubNonCompositeProduct->method('getContext')->willReturn($this->createMock(Context::class));

        $snippets = $this->renderer->render($stubNonCompositeProduct);

        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, ...$snippets);
        $this->assertSnippetContent(json_encode([]), $snippet);
    }

    public function testReturnsAnEmptyAssociatedProductsJsonArraySnippetForNonCompositeProducts(): void
    {
        /** @var ProductView|MockObject $stubNonCompositeProduct */
        $stubNonCompositeProduct = $this->createMock(ProductView::class);
        $stubNonCompositeProduct->method('getContext')->willReturn($this->createMock(Context::class));

        $snippets = $this->renderer->render($stubNonCompositeProduct);

        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, ...$snippets);
        $this->assertSnippetContent(json_encode([]), $snippet);
    }

    public function testReturnsVariationAttributesJsonSnippet(): void
    {
        $snippets = $this->renderer->render($this->stubCompositeProductView);
        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, ...$snippets);

        $this->assertSnippetContent(json_encode($this->testVariationAttributesJsonData), $snippet);
    }

    public function testReturnsAssociatedProductsJsonSnippet(): void
    {
        $snippets = $this->renderer->render($this->stubCompositeProductView);
        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, ...$snippets);

        $this->assertSnippetContent(json_encode($this->testAssociatedAttributesJsonData), $snippet);
    }
}
