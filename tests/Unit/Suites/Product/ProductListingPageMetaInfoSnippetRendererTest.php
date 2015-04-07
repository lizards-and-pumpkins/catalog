<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\SnippetRenderer;
use Brera\SnippetResult;
use Brera\UrlPathKeyGenerator;

/**
 * @covers \Brera\Product\ProductListingCriteriaSnippetRenderer
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetContent
 * @uses   \Brera\SnippetResult
 */
class ProductListingCriteriaSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingCriteriaSnippetRenderer
     */
    private $renderer;

    /**
     * @return ProductListingSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockProductListingSource()
    {
        $mockProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $mockProductListingSource->expects($this->once())
            ->method('getContextData')
            ->willReturn([]);
        $mockProductListingSource->expects($this->once())
            ->method('getCriteria')
            ->willReturn([]);
        return $mockProductListingSource;
    }

    protected function setUp()
    {
        $stubContext = $this->getMock(Context::class);

        $mockUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class);
        $mockUrlPathKeyGenerator->expects($this->any())
            ->method('getUrlKeyForPathInContext')
            ->willReturn('foo');
        $mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $mockContextBuilder->expects($this->any())
            ->method('getContext')
            ->willReturn($stubContext);

        $this->renderer = new ProductListingCriteriaSnippetRenderer($mockUrlPathKeyGenerator, $mockContextBuilder);
    }

    /**
     * @test
     */
    public function itShouldImplementSnippetRendererInterface()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    /**
     * @test
     */
    public function itShouldReturnSnippetResultWithAValidJsonAsAContent()
    {
        $mockProductListingSource = $this->getMockProductListingSource();

        $snippetResult = $this->renderer->render($mockProductListingSource);

        json_decode($snippetResult->getContent());

        $this->assertInstanceOf(SnippetResult::class, $snippetResult);
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    }

    /**
     * @test
     */
    public function theReturnedResultSnippetKeyShouldHaveTheProductListingSnippetCodePrefix()
    {
        $mockProductListingSource = $this->getMockProductListingSource();

        $snippetResult = $this->renderer->render($mockProductListingSource);
        $expectedPattern = ProductListingSnippetRenderer::CODE . '_%s';
        $this->assertStringMatchesFormat($expectedPattern, $snippetResult->getKey());

    }
}
