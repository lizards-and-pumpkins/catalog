<?php

namespace Brera\Renderer;

use Brera\Renderer\Stubs\StubBlock;
use Brera\Renderer\Stubs\StubBlockRenderer;
use Brera\ThemeLocator;
use Brera\Translation\Translator;

/**
 * @covers \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Renderer\Block
 * @uses   \Brera\Renderer\BlockStructure
 */
class BlockRendererTest extends AbstractBlockRendererTest
{
    /**
     * @param ThemeLocator $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @param Translator $stubTranslator
     * @return StubBlockRenderer
     */
    protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        Translator $stubTranslator
    ) {
        return new StubBlockRenderer($stubThemeLocator, $stubBlockStructure, $stubTranslator);
    }

    public function testExceptionIsThrownIfNoRootBlockIsDefined()
    {
        $this->getStubLayout()->method('getNodeChildren')->willReturn([]);
        $this->setExpectedException(BlockRendererMustHaveOneRootBlockException::class);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfMoreThenOneRootBlockIsDefined()
    {
        $this->getStubLayout()->method('getNodeChildren')->willReturn([['test-dummy-1'], ['test-dummy-2']]);
        $this->setExpectedException(BlockRendererMustHaveOneRootBlockException::class);

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfNoBlockClassIsSpecified()
    {
        $this->addStubRootBlock(null, 'dummy-template');
        $this->setExpectedException(CanNotInstantiateBlockException::class, 'Block class is not specified.');

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfTheClassDoesNotExist()
    {
        $this->addStubRootBlock('None\\Existing\\BlockClass', 'dummy-template');
        $this->setExpectedException(CanNotInstantiateBlockException::class, 'Block class does not exist');

        $this->getBlockRenderer()->render('test-projection-source-data', $this->getStubContext());
    }

    public function testExceptionIsThrownIfTheSpecifiedClassIsNotABlock()
    {
        $nonBlockClass = __CLASS__;
        $this->setExpectedException(
            CanNotInstantiateBlockException::class,
            sprintf('Block class "%s" must extend "%s"', $nonBlockClass, Block::class)
        );
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
        $this->setExpectedException(
            MethodNotYetAvailableException::class,
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
}
