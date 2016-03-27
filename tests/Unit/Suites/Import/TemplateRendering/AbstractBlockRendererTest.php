<?php

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\BlockStructure;
use LizardsAndPumpkins\Import\TemplateRendering\Layout;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use LizardsAndPumpkins\TestFileFixtureTrait;

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

    /**
     * @var BaseUrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockBaseUrlBuilder;

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
     * @return Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getStubTranslator()
    {
        return $this->stubTranslator;
    }

    /**
     * @return BaseUrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getMockBaseUrlBuilder()
    {
        return $this->mockBaseUrlBuilder;
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
        $stubBlock->method('getNodeChildren')->willReturn([$stubChild]);
        $stubBlock->method('hasChildren')->willReturn(true);

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
        $stubBlockLayout->method('getAttribute')->willReturnMap([
            ['class', $className],
            ['template', $template],
            ['name', $nameInLayout],
        ]);
        return $stubBlockLayout;
    }

    /**
     * @param ThemeLocator $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @param TranslatorRegistry $stubTranslatorRegistry
     * @param BaseUrlBuilder $baseUrlBuilder
     * @return BlockRenderer
     */
    abstract protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $baseUrlBuilder
    );

    protected function setUp()
    {
        $this->stubLayout = $this->getMock(Layout::class, [], [], '', false);
        $this->stubThemeLocator = $this->getMock(ThemeLocator::class, [], [], '', false);
        $this->stubThemeLocator->method('getLayoutForHandle')->willReturn($this->stubLayout);

        $this->stubBlockStructure = new BlockStructure();

        $this->stubTranslator = $this->getMock(Translator::class, [], [], '', false);
        
        $this->mockBaseUrlBuilder = $this->getMock(BaseUrlBuilder::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->getMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->blockRenderer = $this->createRendererInstance(
            $this->stubThemeLocator,
            $this->stubBlockStructure,
            $stubTranslatorRegistry,
            $this->mockBaseUrlBuilder
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
}
