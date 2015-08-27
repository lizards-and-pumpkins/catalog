<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Product\DefaultNumberOfProductsPerPageSnippetRenderer
 * @uses   \Brera\Snippet
 */
class DefaultNumberOfProductsPerPageSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var DefaultNumberOfProductsPerPageSnippetRenderer
     */
    private $renderer;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $this->stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new DefaultNumberOfProductsPerPageSnippetRenderer(
            $this->mockSnippetList,
            $this->stubSnippetKeyGenerator
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetListIsReturned()
    {
        $dummyNumberOfProductsPerPage = 9;
        $dummySnippetKey = 'bar';

        /** @var ProductListingSourceList|\PHPUnit_Framework_MockObject_MockObject $stubProductListingSourceList */
        $stubProductListingSourceList = $this->getMock(ProductListingSourceList::class, [], [], '', false);
        $stubProductListingSourceList->method('getListOfAvailableNumberOfProductsPerPageForContext')
            ->willReturn([$dummyNumberOfProductsPerPage]);

        $stubContext = $this->getMock(Context::class);
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $stubContextSource->method('getContextsForParts')->willReturn([$stubContext]);

        $this->stubSnippetKeyGenerator->method('getContextPartsUsedForKey')->willReturn(['foo']);
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($dummySnippetKey);

        $expectedSnippet = Snippet::create($dummySnippetKey, $dummyNumberOfProductsPerPage);

        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render($stubProductListingSourceList, $stubContextSource);
    }
}
