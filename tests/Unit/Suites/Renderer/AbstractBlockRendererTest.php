<?php

namespace Brera\Renderer;

use Brera\Context\Context;
use Brera\Renderer\Translation\Translator;
use Brera\TestFileFixtureTrait;

abstract class AbstractBlockRendererTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTranslator;

    protected function setUp()
    {
        $this->stubLayout = $this->getMock(Layout::class, [], [], '', false);
        $this->stubThemeLocator = $this->getMock(ThemeLocator::class);
        $this->stubThemeLocator->method('getLayoutForHandle')->willReturn($this->stubLayout);

        $this->stubBlockStructure = new BlockStructure();
        $this->stubTranslator = $this->getMock(Translator::class);

        $this->blockRenderer = $this->createRendererInstance(
            $this->stubThemeLocator,
            $this->stubBlockStructure,
            $this->stubTranslator
        );
    }

    public function testBlockRendererAbstractClassIsExtended()
    {
        $this->assertInstanceOf(BlockRenderer::class, $this->blockRenderer);
    }

    public function testBlockLayoutHandleIsNonEmptyString()
    {
        $result = $this->blockRenderer->getLayoutHandle();

        $this->assertInternalType('string', $result);
        $this->assertNotEmpty(trim($result));
    }

    public function testBlockRendererClassIsExtended()
    {
        $this->assertInstanceOf(BlockRenderer::class, $this->blockRenderer);
    }

    public function testStringTranslationIsDelegatedToTranslator()
    {
        $originalString = 'foo';
        $translatedString = 'bar';

        $this->stubTranslator->method('translate')->with($originalString)->willReturn($translatedString);

        $this->assertSame($translatedString, $this->blockRenderer->translate($originalString));
    }

    /**
     * @param ThemeLocator $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @param Translator $stubTranslator
     * @return BlockRenderer
     */
    abstract protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        Translator $stubTranslator
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
        $stubBlock->method('getNodeChildren')
            ->willReturn([$stubChild]);
        $stubBlock->method('hasChildren')
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
        $stubBlockLayout->method('getAttribute')
            ->willReturnMap([
                ['class', $className],
                ['template', $template],
                ['name', $nameInLayout],
            ]);
        return $stubBlockLayout;
    }
}
