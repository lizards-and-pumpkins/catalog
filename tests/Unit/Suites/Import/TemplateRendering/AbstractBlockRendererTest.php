<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use LizardsAndPumpkins\Util\FileSystem\TestFileFixtureTrait;
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
     * @var Layout|MockObject
     */
    private $stubLayout;

    /**
     * @var Translator|MockObject
     */
    private $stubTranslator;

    /**
     * @var BaseUrlBuilder|MockObject
     */
    private $mockBaseUrlBuilder;

    /**
     * @var BaseUrlBuilder|MockObject
     */
    private $mockAssetsBaseUrlBuilder;

    /**
     * @return Layout|MockObject
     */
    final protected function getStubLayout(): Layout
    {
        return $this->stubLayout;
    }

    final protected function getBlockRenderer() : BlockRenderer
    {
        return $this->blockRenderer;
    }

    /**
     * @return Context|MockObject
     */
    final protected function getStubContext(): Context
    {
        return $this->createMock(Context::class);
    }

    /**
     * @return Translator|MockObject
     */
    final protected function getStubTranslator(): Translator
    {
        return $this->stubTranslator;
    }

    /**
     * @return BaseUrlBuilder|MockObject
     */
    final protected function getMockBaseUrlBuilder(): BaseUrlBuilder
    {
        return $this->mockBaseUrlBuilder;
    }

    /**
     * @return BaseUrlBuilder|MockObject
     */
    final protected function getMockAssetsBaseUrlBuilder(): BaseUrlBuilder
    {
        return $this->mockAssetsBaseUrlBuilder;
    }

    /**
     * @param string $className
     * @param string $template
     * @return Layout|MockObject
     */
    final protected function addStubRootBlock($className, string $template) : Layout
    {
        return $this->addChildLayoutToStubBlock($this->stubLayout, $className, $template);
    }

    /**
     * @param MockObject $stubBlock
     * @param string $className
     * @param string $template
     * @param string $childBlockName
     * @return Layout|MockObject
     */
    final protected function addChildLayoutToStubBlock(
        MockObject $stubBlock,
        $className,
        string $template,
        string $childBlockName = ''
    ) : Layout {
        $stubChild = $this->createStubBlockLayout($className, $template, $childBlockName);
        $stubBlock->method('getNodeChildren')->willReturn([$stubChild]);
        $stubBlock->method('hasChildren')->willReturn(true);

        return $stubChild;
    }

    /**
     * @param string $className
     * @param string $template
     * @param string $nameInLayout
     * @return Layout
     */
    final protected function createStubBlockLayout($className, string $template, string $nameInLayout = '') : Layout
    {
        $stubBlockLayout = $this->createMock(Layout::class);
        $stubBlockLayout->method('getAttribute')->willReturnMap([
            ['class', $className],
            ['template', $template],
            ['name', $nameInLayout],
        ]);
        return $stubBlockLayout;
    }

    abstract protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $baseUrlBuilder,
        BaseUrlBuilder $assetsBaseUrlBuilder
    ) : BlockRenderer;

    final protected function setUp(): void
    {
        $this->stubLayout = $this->createMock(Layout::class);
        $stubThemeLocator = $this->createMock(ThemeLocator::class);
        $stubThemeLocator->method('getLayoutForHandle')->willReturn($this->stubLayout);

        $stubBlockStructure = new BlockStructure();

        $this->stubTranslator = $this->createMock(Translator::class);
        
        $this->mockBaseUrlBuilder = $this->createMock(BaseUrlBuilder::class);
        $this->mockAssetsBaseUrlBuilder = $this->createMock(BaseUrlBuilder::class);

        /** @var TranslatorRegistry|MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->createMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->blockRenderer = $this->createRendererInstance(
            $stubThemeLocator,
            $stubBlockStructure,
            $stubTranslatorRegistry,
            $this->mockBaseUrlBuilder,
            $this->mockAssetsBaseUrlBuilder
        );
    }

    public function testBlockRendererAbstractClassIsExtended(): void
    {
        $this->assertInstanceOf(BlockRenderer::class, $this->blockRenderer);
    }

    public function testBlockLayoutHandleIsNonEmptyString(): void
    {
        $result = $this->blockRenderer->getLayoutHandle();

        $this->assertIsString($result);
        $this->assertNotEmpty(trim($result));
    }

    public function testBlockRendererClassIsExtended(): void
    {
        $this->assertInstanceOf(BlockRenderer::class, $this->blockRenderer);
    }
}
