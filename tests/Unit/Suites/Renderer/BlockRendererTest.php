<?php


namespace Brera\Renderer;

use Brera\Context\Context;
use Brera\ProjectionSourceData;
use Brera\Renderer\Stubs\StubBlock;
use Brera\Renderer\Stubs\StubBlockRenderer;
use Brera\TestFileFixtureTrait;
use Brera\ThemeLocator;

/**
 * @covers \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Renderer\Block
 */
class BlockRendererTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->stubThemeLocator = $this->getMock(ThemeLocator::class);
        $this->stubLayout = $this->getMock(Layout::class, [], [], '', false);
        $this->stubThemeLocator->expects($this->any())
            ->method('getLayoutForHandle')
            ->willReturn($this->stubLayout);
        $this->stubBlockStructure = new BlockStructure();
        $this->blockRenderer = new StubBlockRenderer($this->stubThemeLocator, $this->stubBlockStructure);
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\BlockRendererMustHaveOneRootBlockException
     */
    public function itShouldThrowAnExceptionIfNoRootBlockIsDefined()
    {
        $this->stubLayout->expects($this->any())
            ->method('getNodeChildren')
            ->willReturn([]);
        $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\BlockRendererMustHaveOneRootBlockException
     */
    public function itShouldThrowAnExceptionIfMoreThenOneRootBlockIsDefined()
    {
        $this->stubLayout->expects($this->any())
            ->method('getNodeChildren')
            ->willReturn([['test-dummy-1'], ['test-dummy-2']]);
        $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\CanNotInstantiateBlockException
     * @expectedExceptionMessage Block class is not specified.
     */
    public function itShouldThrowAnExceptionIfNoBlockClassIsSpecified()
    {
        $this->addStubRootBlock(null, 'dummy-template');
        $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\CanNotInstantiateBlockException
     * @expectedExceptionMessage Block class does not exist
     */
    public function itShouldThrowAnExceptionIfTheClassDoesNotExist()
    {
        $this->addStubRootBlock('None\\Existing\\BlockClass', 'dummy-template');
        $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfTheSpecifiedClassIsNotABlock()
    {
        $nonBlockClass = __CLASS__;
        $this->setExpectedException(
            CanNotInstantiateBlockException::class,
            sprintf('Block class "%s" must extend "%s"', $nonBlockClass, Block::class)
        );
        $this->addStubRootBlock($nonBlockClass, 'dummy-template');
        $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
    }

    /**
     * @test
     */
    public function itShouldRenderABlockSpecifiedInLayout()
    {
        $template = sys_get_temp_dir() . '/' . uniqid() . '/test-template.php';
        $templateContent = 'test template content';
        $this->createFixtureFile($template, $templateContent);
        $this->addStubRootBlock(StubBlock::class, $template);
        $result = $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
        $this->assertEquals($templateContent, $result);
    }

    /**
     * @test
     */
    public function itShouldRenderChildrenRecursively()
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

        $result = $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
        $this->assertEquals($combinedTemplateContent, $result);
    }

    /**
     * @test
     */
    public function itShouldInsertAPlaceholderIfChildBlockIsMissing()
    {
        $childBlockName = 'child-block';
        $outputChildBlockStatement = '<?= $this->getChildOutput("' . $childBlockName . '") ?>';
        $rootTemplateContent = 'Root template with ::' . $outputChildBlockStatement . '::';
        $templateContentWithChildPlaceholder = 'Root template with ::{{' . $childBlockName . '}}::';

        $rootTemplate = $this->getUniqueTempDir() . '/root-template.php';
        $this->createFixtureFile($rootTemplate, $rootTemplateContent);

        $rootBlock = $this->addStubRootBlock(StubBlock::class, $rootTemplate);

        $result = $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
        $this->assertEquals($templateContentWithChildPlaceholder, $result);
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\MethodNotYetAvailableException
     * @expectedExceptionMessage The method "getNestedSnippetCodes()" can not be called before "render()" is executed
     */
    public function itShouldThrowAnExceptionIfTheListOfNestedSnippetsIsFetchedBeforeRendering()
    {
        $this->blockRenderer->getNestedSnippetCodes();
    }

    /**
     * @test
     */
    public function itShouldReturnAnArrayOfMissingChildBlockNames()
    {
        $childBlockName1 = 'child-block1';
        $childBlockName2 = 'child-block2';
        $outputChildBlockStatement1 = '<?= $this->getChildOutput("' . $childBlockName1 . '") ?>';
        $outputChildBlockStatement2 = '<?= $this->getChildOutput("' . $childBlockName2 . '") ?>';
        $rootTemplateContent = '::' . $outputChildBlockStatement1 . $outputChildBlockStatement2 . '::';

        $rootTemplate = $this->getUniqueTempDir() . '/root-template.php';
        $this->createFixtureFile($rootTemplate, $rootTemplateContent);

        $rootBlock = $this->addStubRootBlock(StubBlock::class, $rootTemplate);

        $this->blockRenderer->render($this->getStubDataObject(), $this->getStubContext());
        $this->assertEquals([$childBlockName1, $childBlockName2], $this->blockRenderer->getNestedSnippetCodes());
    }

    /**
     * @test
     */
    public function itShouldReturnTheLayoutHandleAsTheRootSnippetCode()
    {
        $this->assertEquals(StubBlockRenderer::LAYOUT_HANDLE, $this->blockRenderer->getRootSnippetCode());
    }

    /**
     * @return ProjectionSourceData|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubDataObject()
    {
        return $this->getMock(ProjectionSourceData::class);
    }

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubContext()
    {
        return $this->getMock(Context::class, [], [], '', false);
    }

    /**
     * @param $className
     * @param $template
     * @return Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    private function addStubRootBlock($className, $template)
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
    private function addChildLayoutToStubBlock(
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
    private function createStubBlockLayout($className, $template, $nameInLayout = '')
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
