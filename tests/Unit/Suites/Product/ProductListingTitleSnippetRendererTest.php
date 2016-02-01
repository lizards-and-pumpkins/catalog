<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

class ProductListingTitleSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testSnippetKey = ProductListingTitleSnippetRenderer::CODE . '_foo';

    private $testProductListingUrlKey = 'foo';

    /**
     * @var ProductListingTitleSnippetRenderer
     */
    private $renderer;

    /**
     * @var ProductListingCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingCriteria;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingTitleSnippetKeyGenerator;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    protected function setUp()
    {
        $this->stubProductListingTitleSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductListingTitleSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testSnippetKey);
        $this->stubContextBuilder = $this->getMock(ContextBuilder::class);
        $this->stubContextBuilder->method('createContext')->willReturn($this->getMock(Context::class));
        $this->renderer = new ProductListingTitleSnippetRenderer(
            $this->stubProductListingTitleSnippetKeyGenerator,
            $this->stubContextBuilder
        );
        $this->stubProductListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);
        $this->stubProductListingCriteria->method('getContextData')->willReturn([]);
        $this->stubProductListingCriteria->method('getUrlKey')->willReturn($this->testProductListingUrlKey);
    }
    
    public function testItImplementsTheSnippetRendererInterface()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testItReturnsAProductListingTitleSnippet()
    {
        $result = $this->renderer->render($this->stubProductListingCriteria);
        
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $result);
        $this->assertSame($this->testSnippetKey, $result[0]->getKey());
        $this->assertSame($this->testProductListingUrlKey, $result[0]->getContent());
    }
}
