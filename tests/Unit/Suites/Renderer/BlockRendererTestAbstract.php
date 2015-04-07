<?php

namespace Brera\Renderer;

use Brera\Context\Context;
use Brera\ProjectionSourceData;
use Brera\TestFileFixtureTrait;
use Brera\ThemeLocator;

abstract class BlockRendererTestAbstract extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var BlockRenderer
     */
    private $blockRenderer;

    /**
     * @var ThemeLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubThemeLocator;

    /**
     * @var Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLayout;

    /**
     * @var BlockStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubBlockStructure;

    protected function setUp()
    {
        $this->stubThemeLocator = $this->getMock(ThemeLocator::class);
        $this->stubLayout = $this->getMock(Layout::class, [], [], '', false);
        $this->stubThemeLocator->expects($this->any())
            ->method('getLayoutForHandle')
            ->willReturn($this->stubLayout);
        $this->stubBlockStructure = new BlockStructure();
        $this->blockRenderer = $this->createRendererInstance($this->stubThemeLocator, $this->stubBlockStructure);
    }
    /**
     * @param ThemeLocator|\PHPUnit_Framework_MockObject_MockObject $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @return BlockRenderer
     */
    abstract protected function createRendererInstance(
        \PHPUnit_Framework_MockObject_MockObject $stubThemeLocator,
        BlockStructure $stubBlockStructure
    );

    /**
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getStubLayout()
    {
        return $this->stubLayout;
    }

    /**
     * @return BlockRenderer
     */
    final protected function getBlockRenderer()
    {
        return $this->blockRenderer;
    }

    /**
     * @return ProjectionSourceData|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getStubDataObject()
    {
        return $this->getMock(ProjectionSourceData::class);
    }

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getStubContext()
    {
        return $this->getMock(Context::class, [], [], '', false);
    }

    /**
     * @param string $className
     * @param string $template
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function addStubRootBlock($className, $template)
    {
        return $this->addChildLayoutToStubBlock($this->stubLayout, $className, $template);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $stubBlock
     * @param string $className
     * @param string $template
     * @param string $childBlockName
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function addChildLayoutToStubBlock(
        \PHPUnit_Framework_MockObject_MockObject $stubBlock,
        $className,
        $template,
        $childBlockName = ''
    ) {
        $stubChild = $this->createStubBlockLayout($className, $template, $childBlockName);
        $stubBlock->expects($this->any())
            ->method('getNodeChildren')
            ->willReturn([$stubChild]);
        $stubBlock->expects($this->any())
            ->method('hasChildren')
            ->willReturn(true);
        return $stubChild;
    }

    /**
     * @param string $className
     * @param string $template
     * @param string $nameInLayout
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function createStubBlockLayout($className, $template, $nameInLayout = '')
    {
        $stubBlockLayout = $this->getMock(Layout::class, [], [], '', false);
        $stubBlockLayout->expects($this->any())
            ->method('getAttribute')
            ->willReturnMap([
                ['class', $className],
                ['template', $template],
                ['name', $nameInLayout],
            ]);
        return $stubBlockLayout;
    }
}
