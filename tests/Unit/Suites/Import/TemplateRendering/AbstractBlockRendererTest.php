<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\TestFileFixtureTrait;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractBlockRendererTest extends TestCase
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
     * @var BaseUrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAssetsBaseUrlBuilder;

    /**
     * @var TemplateFactory
     */
    private $templatefactory;

    /**
     * @var Template|MockObject
     */
    protected $templateMock;

    /**
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getStubLayout()
    {
        return $this->stubLayout;
    }

    final protected function getBlockRenderer(): BlockRenderer
    {
        return $this->blockRenderer;
    }

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getStubContext()
    {
        return $this->createMock(Context::class);
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
     * @return BaseUrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getMockAssetsBaseUrlBuilder()
    {
        return $this->mockAssetsBaseUrlBuilder;
    }

    /**
     * @param string $className
     * @param string $template
     *
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function addStubRootBlock($className, string $template): Layout
    {
        return $this->addChildLayoutToStubBlock($this->stubLayout, $className, $template);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $stubBlock
     * @param string $className
     * @param string $template
     * @param string $childBlockName
     *
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function addChildLayoutToStubBlock(
        \PHPUnit_Framework_MockObject_MockObject $stubBlock,
        $className,
        string $template,
        string $childBlockName = ''
    ): Layout {
        $stubChild = $this->createStubBlockLayout($className, $template, $childBlockName);
        $stubBlock->method('getNodeChildren')->willReturn([$stubChild]);
        $stubBlock->method('hasChildren')->willReturn(true);

        return $stubChild;
    }

    /**
     * @param string $className
     * @param string $template
     * @param string $nameInLayout
     *
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function createStubBlockLayout($className, string $template, string $nameInLayout = ''): Layout
    {
        $stubBlockLayout = $this->createMock(Layout::class);
        $stubBlockLayout->method('getAttribute')->willReturnMap(
            [
                ['class', $className],
                ['template', $template],
                ['name', $nameInLayout],
            ]
        );

        return $stubBlockLayout;
    }

    abstract protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $baseUrlBuilder,
        BaseUrlBuilder $assetsBaseUrlBuilder,
        TemplateFactory $templateFactory
    ): BlockRenderer;

    protected function setUp()
    {
        $this->stubLayout       = $this->createMock(Layout::class);
        $this->stubThemeLocator = $this->createMock(ThemeLocator::class);
        $this->stubThemeLocator->method('getLayoutForHandle')->willReturn($this->stubLayout);

        $this->stubBlockStructure = new BlockStructure();

        $this->stubTranslator = $this->createMock(Translator::class);

        $this->mockBaseUrlBuilder       = $this->createMock(BaseUrlBuilder::class);
        $this->mockAssetsBaseUrlBuilder = $this->createMock(BaseUrlBuilder::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->createMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->templatefactory = $this->createMock(TemplateFactory::class);
        $this->templateMock    = $this->createMock(Template::class);
        $this->templatefactory->method('createTemplate')->willReturn($this->templateMock);

        $this->blockRenderer = $this->createRendererInstance(
            $this->stubThemeLocator,
            $this->stubBlockStructure,
            $stubTranslatorRegistry,
            $this->mockBaseUrlBuilder,
            $this->mockAssetsBaseUrlBuilder,
            $this->templatefactory
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
