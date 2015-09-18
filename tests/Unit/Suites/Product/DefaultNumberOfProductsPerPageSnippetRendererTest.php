<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\DefaultNumberOfProductsPerPageSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
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

        $stubProductsPerPageForContextList = $this->getMock(ProductsPerPageForContextList::class, [], [], '', false);
        $stubProductsPerPageForContextList->method('getListOfAvailableNumberOfProductsPerPageForContext')
            ->willReturn([$dummyNumberOfProductsPerPage]);

        $stubContext = $this->getMock(Context::class);
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $stubContextSource->method('getContextsForParts')->willReturn([$stubContext]);

        $this->stubSnippetKeyGenerator->method('getContextPartsUsedForKey')->willReturn(['foo']);
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($dummySnippetKey);

        $expectedSnippet = Snippet::create($dummySnippetKey, $dummyNumberOfProductsPerPage);

        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render($stubProductsPerPageForContextList, $stubContextSource);
    }
}
