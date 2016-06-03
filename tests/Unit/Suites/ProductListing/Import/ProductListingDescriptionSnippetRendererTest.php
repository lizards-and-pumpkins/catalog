<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingDescriptionBlockRenderer;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ProductListingDescriptionSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testSnippetKey = ProductListingDescriptionSnippetRenderer::CODE;

    /**
     * @var ProductListingDescriptionSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var ProductListingDescriptionBlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDescriptionBlockRenderer;

    /**
     * @param string[] $attributes
     * @return ProductListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListingWithAttributes(array $attributes)
    {
        $stubSearchCriteria = $this->createMock(CompositeSearchCriterion::class);
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('getContextData')->willReturn([]);
        $stubProductListing->method('getCriteria')->willReturn($stubSearchCriteria);

        $getAttributeValueMap = $hasAttributeValueMap = [];
        foreach ($attributes as $attributeCode => $attributeValue) {
            $getAttributeValueMap[] = [$attributeCode, $attributeValue];
            $hasAttributeValueMap[] = [$attributeCode, true];
        }
        $hasAttributeValueMap[] = [$this->anything(), false];

        $stubProductListing->method('getAttributeValueByCode')->willReturnMap($getAttributeValueMap);
        $stubProductListing->method('hasAttribute')->willReturnMap($hasAttributeValueMap);

        return $stubProductListing;
    }

    /**
     * @param string $snippetKey
     * @param Snippet[] $snippets
     * @return Snippet
     */
    public function findSnippetByKey($snippetKey, array $snippets)
    {
        foreach ($snippets as $snippet) {
            if ($snippet->getKey() === $snippetKey) {
                return $snippet;
            }
        }
        $this->fail(sprintf('Snippet with key "%s" not found in result', $snippetKey));
    }

    protected function setUp()
    {
        $class = ProductListingDescriptionBlockRenderer::class;
        $this->stubDescriptionBlockRenderer = $this->createMock($class);

        $this->mockSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testSnippetKey);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $mockContextBuilder */
        $mockContextBuilder = $this->createMock(ContextBuilder::class);
        $mockContext = $this->createMock(Context::class);
        $mockContextBuilder->method('createContext')->willReturn($mockContext);

        $this->renderer = new ProductListingDescriptionSnippetRenderer(
            $this->stubDescriptionBlockRenderer,
            $this->mockSnippetKeyGenerator,
            $mockContextBuilder
        );
    }

    public function testItIsASnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testItReturnsASnippetList()
    {
        $productListing = $this->createStubProductListingWithAttributes(['description' => 'Test']);
        $this->stubDescriptionBlockRenderer->method('render')->willReturn('Test');
        $result = $this->renderer->render($productListing);

        $this->assertInternalType('array', $result);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $result);
    }

    public function testItReturnsAProductListingDescriptionSnippetIfTheListingHasADescription()
    {
        $productListing = $this->createStubProductListingWithAttributes(['description' => 'Test']);
        $this->stubDescriptionBlockRenderer->method('render')->willReturn('Test');
        $result = $this->renderer->render($productListing);
        $descriptionSnippet = $this->findSnippetByKey($this->testSnippetKey, $result);

        $this->assertSame('Test', $descriptionSnippet->getContent());
    }

    public function testItReturnsNoSnippetIfTheProductListingHasNoDescription()
    {
        $productListing = $this->createStubProductListingWithAttributes([]);
        $result = $this->renderer->render($productListing);

        $this->assertCount(0, $result);
    }
}
