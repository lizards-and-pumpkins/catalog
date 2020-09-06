<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockRendererMustHaveOneRootBlockException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\CanNotInstantiateBlockException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\MethodNotYetAvailableException;
use LizardsAndPumpkins\Import\TemplateRendering\Stub\StubBlock;
use LizardsAndPumpkins\Import\TemplateRendering\Stub\StubBlockRenderer;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\Block
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockStructure
 */
class BlockRendererTest extends AbstractBlockRendererTest
{
    final protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $baseUrlBuilder,
        BaseUrlBuilder $stubAssetsBaseUrlBuilder
    ): BlockRenderer {
        return new StubBlockRenderer(
            $stubThemeLocator,
            $stubBlockStructure,
            $stubTranslatorRegistry,
            $baseUrlBuilder,
            $stubAssetsBaseUrlBuilder
        );
    }

    public function testExceptionIsThrownIfNoRootBlockIsDefined(): void
    {
        $this->getStubLayout()->method('getNodeChildren')->willReturn([]);
        $this->expectException(BlockRendererMustHaveOneRootBlockException::class);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfMoreThenOneRootBlockIsDefined(): void
    {
        $this->getStubLayout()->method('getNodeChildren')->willReturn([['test-dummy-1'], ['test-dummy-2']]);
        $this->expectException(BlockRendererMustHaveOneRootBlockException::class);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfNoBlockClassIsSpecified(): void
    {
        $this->addStubRootBlock(null, 'dummy-template');
        $this->expectException(\TypeError::class);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfTheClassDoesNotExist(): void
    {
        $this->addStubRootBlock('None\\Existing\\BlockClass', 'dummy-template');
        $this->expectException(CanNotInstantiateBlockException::class);
        $this->expectExceptionMessage('Block class does not exist');

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfTheSpecifiedClassIsNotABlock(): void
    {
        $nonBlockClass = __CLASS__;
        $this->expectException(CanNotInstantiateBlockException::class);
        $this->expectExceptionMessage(sprintf('Block class "%s" must extend "%s"', $nonBlockClass, Block::class));
        $this->addStubRootBlock($nonBlockClass, 'dummy-template');
        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testBlockSpecifiedInLayoutIsRendered(): void
    {
        $template = sys_get_temp_dir() . '/' . uniqid() . '/test-template.php';
        $templateContent = 'test template content';
        $this->createFixtureFile($template, $templateContent);
        $this->addStubRootBlock(StubBlock::class, $template);
        $result = $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());

        $this->assertEquals($templateContent, $result);
    }

    public function testChildrenBlocksAreRenderedRecursively(): void
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

    public function testPlaceholderIsInsertedIfChildBlockIsMissing(): void
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

    public function testExceptionIsThrownIfTheListOfNestedSnippetsIsFetchedBeforeRendering(): void
    {
        $this->expectException(MethodNotYetAvailableException::class);
        $this->expectExceptionMessage(
            'The method "getNestedSnippetCodes()" can not be called before "render()" is executed'
        );
        $this->getBlockRenderer()->getNestedSnippetCodes();
    }

    public function testArrayOfMissingChildBlockNamesIsReturned(): void
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

    public function testFreshListOfMissingChildrenBlockNamesIsReturnedIfRenderIsCalledTwice(): void
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

    public function testLayoutHandleIsReturnedAsRootSnippetCode(): void
    {
        $this->assertEquals(StubBlockRenderer::LAYOUT_HANDLE, $this->getBlockRenderer()->getRootSnippetCode());
    }

    public function testDataObjectPassedToRenderIsReturned(): void
    {
        $testProjectionSourceData = 'test-projection-source-data';
        $template = $this->getUniqueTempDir() . '/template.phtml';
        $this->createFixtureFile($template, '');
        $this->addStubRootBlock(StubBlock::class, $template);
        $this->getBlockRenderer()->render($testProjectionSourceData, $this->getStubContext());
        $this->assertSame($testProjectionSourceData, $this->getBlockRenderer()->getDataObject());
    }

    public function testStringTranslationIsDelegatedToTranslator(): void
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

    public function testItDelegatesGettingTheBaseUrlToTheBaseUrlBuilder(): void
    {
        $this->getMockBaseUrlBuilder()->expects($this->once())->method('create')->with($this->getStubContext());

        $testDir = $this->getUniqueTempDir();
        $this->createFixtureDirectory($testDir);
        $this->createFixtureFile($testDir . '/test-template.php', 'test template content');
        $this->addStubRootBlock(StubBlock::class, $testDir . '/test-template.php');
        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());

        $this->getBlockRenderer()->getBaseUrl();
    }

    public function testWebsiteCodeIsReturned(): void
    {
        $testWebsiteCode = 'foo';
        $stubContext = $this->getStubContext();
        $stubContext->method('getValue')->with(Website::CONTEXT_CODE)->willReturn($testWebsiteCode);

        $dataObject = [];
        $testDir = $this->getUniqueTempDir();
        $this->createFixtureDirectory($testDir);
        $this->createFixtureFile($testDir . '/test-template.php', 'test template content');
        $this->addStubRootBlock(StubBlock::class, $testDir . '/test-template.php');

        $this->getBlockRenderer()->render($dataObject, $stubContext);

        $this->assertSame($testWebsiteCode, $this->getBlockRenderer()->getWebsiteCode());
    }

    public function testDelegatesGettingTheAssetBaseUrlToTheAssetsBaseUrlBuilder(): void
    {
        $this->getMockAssetsBaseUrlBuilder()->expects($this->once())->method('create')->with($this->getStubContext());

        $testDir = $this->getUniqueTempDir();
        $this->createFixtureDirectory($testDir);
        $this->createFixtureFile($testDir . '/test-template.php', 'test template content');
        $this->addStubRootBlock(StubBlock::class, $testDir . '/test-template.php');
        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());

        $this->getBlockRenderer()->getAssetsBaseUrl();
    }
}
