<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockRendererTestAbstract;
use Brera\Renderer\BlockStructure;
use Brera\Renderer\Stubs\StubBlock;
use Brera\ThemeLocator;

/**
 * @covers \Brera\Product\ProductInListingBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Renderer\BlockStructure
 * @uses   \Brera\Renderer\Block
 */
class ProductInListingBlockRendererTest extends BlockRendererTestAbstract
{
    /**
     * @test
     */
    public function itShouldUseTheLayoutHandleProductInListing()
    {
        $renderer = $this->getBlockRenderer();
        $method = new \ReflectionMethod($renderer, 'getLayoutHandle');
        $method->setAccessible(true);
        $this->assertEquals('product_in_listing', $method->invoke($renderer));
    }

    /**
     * @test
     */
    public function itShouldReturnTheProductPassedToRender()
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubContext = $this->getStubContext();
        $template = $this->getUniqueTempDir() . '/template.phtml';
        $this->createFixtureFile($template, '');
        $this->addStubRootBlock(StubBlock::class, $template);
        $this->getBlockRenderer()->render($stubProduct, $stubContext);
        $this->assertSame($stubProduct, $this->getBlockRenderer()->getProduct());
    }

    /**
     * @param ThemeLocator|\PHPUnit_Framework_MockObject_MockObject $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @return BlockRenderer
     */
    protected function createRendererInstance(
        \PHPUnit_Framework_MockObject_MockObject $stubThemeLocator,
        BlockStructure $stubBlockStructure
    ) {
        return new ProductInListingBlockRenderer($stubThemeLocator, $stubBlockStructure);
    }
}
