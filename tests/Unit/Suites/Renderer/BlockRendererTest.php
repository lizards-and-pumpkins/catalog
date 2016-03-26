<?php

namespace LizardsAndPumpkins\Renderer;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Import\ContentBlock\Block;
use LizardsAndPumpkins\Import\TemplateRendering\BlockStructure;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockRendererMustHaveOneRootBlockException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\CanNotInstantiateBlockException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\MethodNotYetAvailableException;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Renderer\Stubs\StubBlock;
use LizardsAndPumpkins\Renderer\Stubs\StubBlockRenderer;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\Block
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockStructure
 */
class BlockRendererTest extends AbstractBlockRendererTest
{
    /**
     * @param ThemeLocator $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @param TranslatorRegistry $stubTranslatorRegistry
     * @param BaseUrlBuilder $baseUrlBuilder
     * @return StubBlockRenderer
     */
    protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $baseUrlBuilder
    ) {
        return new StubBlockRenderer($stubThemeLocator, $stubBlockStructure, $stubTranslatorRegistry, $baseUrlBuilder);
    }

    public function testExceptionIsThrownIfNoRootBlockIsDefined()
    {
        $this->getStubLayout()->method('getNodeChildren')->willReturn([]);
        $this->expectException(BlockRendererMustHaveOneRootBlockException::class);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfMoreThenOneRootBlockIsDefined()
    {
        $this->getStubLayout()->method('getNodeChildren')->willReturn([['test-dummy-1'], ['test-dummy-2']]);
        $this->expectException(BlockRendererMustHaveOneRootBlockException::class);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfNoBlockClassIsSpecified()
    {
        $this->addStubRootBlock(null, 'dummy-template');
        $this->expectException(CanNotInstantiateBlockException::class);
        $this->expectExceptionMessage('Block class is not specified.');

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfTheClassDoesNotExist()
    {
        $this->addStubRootBlock('None\\Existing\\BlockClass', 'dummy-template');
        $this->expectException(CanNotInstantiateBlockException::class);
        $this->expectExceptionMessage('Block class does not exist');

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfTheSpecifiedClassIsNotABlock()
    {
        $nonBlockClass = __CLASS__;
        $this->expectException(CanNotInstantiateBlockException::class);
        $this->expectExceptionMessage(sprintf('Block class "%s" must extend "%s"', $nonBlockClass, Block::class));
        $this->addStubRootBlock($nonBlockClass, 'dummy-template');
        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testBlockSpecifiedInLayoutIsRendered()
    {
        $template = sys_get_temp_dir() . '/' . uniqid() . '/test-template.php';
        $templateContent = 'test template content';
        $this->createFixtureFile($template, $templateContent);
        $this->addStubRootBlock(StubBlock::class, $template);
        $result = $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());

        $this->assertEquals($templateContent, $result);
    }

    public function testChildrenBlocksAreRenderedRecursively()
    {
        $childBlockName = 'child-block';
        $outputChildBlockStatement = '<?= $this->getChildOutput("' . $childBlockName . '") ?>';
        $rootTemplateContent = 'Root template with ::' . $outputChildBlockStatement . '::';
        $childTemplateContent = 'Child template content';
        $combinedTemplateContent = 'Root template with ::Child template content::';

        $rootTemplate = $this->getUniqueTempDir() . '/root-template.php';
        $childTemplate = $this->getUniqueTempDir() . '/child-template.php';
        $this->createFixtureFile($rootTemplate, $rootTemplateContent);
        $this->createFixtureFile($childTemplate, $childTemplateContent);

        $rootBlock = $this->addStubRootBlock(StubBlock::class, $rootTemplate);
        $this->addChildLayoutToStubBlock($rootBlock, StubBlock::class, $childTemplate, $childBlockName);

        $result = $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());

        $this->assertEquals($combinedTemplateContent, $result);
    }

    public function testPlaceholderIsInsertedIfChildBlockIsMissing()
    {
        $childBlockName = 'child-block';
        $outputChildBlockStatement = '<?= $this->getChildOutput("' . $childBlockName . '") ?>';
        $rootTemplateContent = 'Root template with ::' . $outputChildBlockStatement . '::';
        $templateContentWithChildPlaceholder = 'Root template with ::{{snippet ' . $childBlockName . '}}::';

        $rootTemplate = $this->getUniqueTempDir() . '/root-template.php';
        $this->createFixtureFile($rootTemplate, $rootTemplateContent);

        $this->addStubRootBlock(StubBlock::class, $rootTemplate);

        $result = $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
        $this->assertEquals($templateContentWithChildPlaceholder, $result);
    }

    public function testExceptionIsThrownIfTheListOfNestedSnippetsIsFetchedBeforeRendering()
    {
        $this->expectException(MethodNotYetAvailableException::class);
        $this->expectExceptionMessage(
            'The method "getNestedSnippetCodes()" can not be called before "render()" is executed'
        );
        $this->getBlockRenderer()->getNestedSnippetCodes();
    }

    public function testArrayOfMissingChildBlockNamesIsReturned()
    {
        $childBlockName1 = 'child-block1';
        $childBlockName2 = 'child-block2';
        $outputChildBlockStatement1 = '<?= $this->getChildOutput("' . $childBlockName1 . '") ?>';
        $outputChildBlockStatement2 = '<?= $this->getChildOutput("' . $childBlockName2 . '") ?>';
        $rootTemplateContent = '::' . $outputChildBlockStatement1 . $outputChildBlockStatement2 . '::';

        $rootTemplate = $this->getUniqueTempDir() . '/root-template.php';
        $this->createFixtureFile($rootTemplate, $rootTemplateContent);

        $this->addStubRootBlock(StubBlock::class, $rootTemplate);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
        $this->assertEquals([$childBlockName1, $childBlockName2], $this->getBlockRenderer()->getNestedSnippetCodes());
    }

    public function testFreshListOfMissingChildrenBlockNamesIsReturnedIfRenderIsCalledTwice()
    {
        $childBlockName1 = 'child-block1';
        $childBlockName2 = 'child-block2';
        $outputChildBlockStatement1 = '<?= $this->getChildOutput("' . $childBlockName1 . '") ?>';
        $outputChildBlockStatement2 = '<?= $this->getChildOutput("' . $childBlockName2 . '") ?>';
        $rootTemplateContent = '::' . $outputChildBlockStatement1 . $outputChildBlockStatement2 . '::';

        $rootTemplate = $this->getUniqueTempDir() . '/root-template.php';
        $this->createFixtureFile($rootTemplate, $rootTemplateContent);

        $this->addStubRootBlock(StubBlock::class, $rootTemplate);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
        $this->assertEquals([$childBlockName1, $childBlockName2], $this->getBlockRenderer()->getNestedSnippetCodes());
        
        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
        $this->assertEquals([$childBlockName1, $childBlockName2], $this->getBlockRenderer()->getNestedSnippetCodes());
    }

    public function testLayoutHandleIsReturnedAsRootSnippetCode()
    {
        $this->assertEquals(StubBlockRenderer::LAYOUT_HANDLE, $this->getBlockRenderer()->getRootSnippetCode());
    }

    public function testDataObjectPassedToRenderIsReturned()
    {
        $testProjectionSourceData = 'test-projection-source-data';
        $template = $this->getUniqueTempDir() . '/template.phtml';
        $this->createFixtureFile($template, '');
        $this->addStubRootBlock(StubBlock::class, $template);
        $this->getBlockRenderer()->render($testProjectionSourceData, $this->getStubContext());
        $this->assertSame($testProjectionSourceData, $this->getBlockRenderer()->getDataObject());
    }

    public function testStringTranslationIsDelegatedToTranslator()
    {
        $originalString = 'foo';
        $translatedString = 'bar';

        $template = sys_get_temp_dir() . '/' . uniqid() . '/test-template.php';
        $templateContent = 'test template content';
        $this->createFixtureFile($template, $templateContent);
        $this->addStubRootBlock(StubBlock::class, $template);
        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());

        $this->getStubTranslator()->method('translate')->with($originalString)->willReturn($translatedString);

        $this->assertSame($translatedString, $this->getBlockRenderer()->translate($originalString));
    }

    public function testItDelegatesGettingTheBaseUrlToTheBaseUrlBuilder()
    {
        $this->getMockBaseUrlBuilder()->expects($this->once())->method('create')->with($this->getStubContext());

        $testDir = $this->getUniqueTempDir();
        $this->createFixtureDirectory($testDir);
        $this->createFixtureFile($testDir . '/test-template.php', 'test template content');
        $this->addStubRootBlock(StubBlock::class, $testDir . '/test-template.php');
        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
        
        $this->getBlockRenderer()->getBaseUrl();
    }
}
