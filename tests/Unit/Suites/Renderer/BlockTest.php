<?php

namespace LizardsAndPumpkins\Renderer;

use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Renderer\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var mixed
     */
    private $testProjectionSourceData = 'test-projection-source-data';

    /**
     * @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockBlockRenderer;
    
    public function setUp()
    {
        $this->mockBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
    }

    public function testBlocksNameIsReturned()
    {
        $blockName = 'test-block-name';
        $instance = $this->createBlockInstance('test-template.phtml', $blockName);

        $this->assertEquals($blockName, $instance->getBlockName());
    }

    public function testDataObjectIsReturned()
    {
        $block = $this->createBlockInstance('test-template.phtml', 'test-block-name');
        $method = new \ReflectionMethod($block, 'getDataObject');
        $method->setAccessible(true);

        $this->assertSame($this->testProjectionSourceData, $method->invoke($block));
    }

    public function testExceptionIsThrownIfTemplateFileDoesNotExist()
    {
        $block = $this->createBlockInstance('test-template.phtml', 'test-block-name');
        $this->setExpectedException(TemplateFileNotReadableException::class);

        $block->render();
    }

    public function testExceptionIsThrownIfTemplateFileIsNotReadable()
    {
        $templateFilePath = $this->getUniqueTempDir() . '/test-template.phtml';

        $this->createFixtureFile($templateFilePath, '', 0000);

        $block = $this->createBlockInstance($templateFilePath, 'test-block-name');

        $this->setExpectedException(TemplateFileNotReadableException::class);

        $block->render();
    }

    public function testSameStringAsTranslationIsReturned()
    {
        $block = $this->createBlockInstance('test-template.phtml', 'test-block-name');
        $result = $block->__('foo');

        $this->assertEquals('foo', $result);
    }

    public function testTemplateIsReturned()
    {
        $template = $this->getUniqueTempDir() . '/test-template.phtml';
        $templateContent = 'The template content';
        $this->createFixtureFile($template, $templateContent);
        $block = $this->createBlockInstance($template, $templateContent);

        $this->assertEquals($templateContent, $block->render());
    }

    public function testGettingChildBlockOutputIsDelegatedToBlockRenderer()
    {
        $blockName = 'test-block-name';
        $childName = 'child-name';
        $this->mockBlockRenderer->expects($this->once())
            ->method('getChildBlockOutput')
            ->with($blockName, $childName);
        $block = $this->createBlockInstance('template.phtml', $blockName);
        $block->getChildOutput($childName);
    }

    public function testLayoutHandleIsReturned()
    {
        $expectedLayoutHandle = 'foo';

        $this->mockBlockRenderer->expects($this->once())
            ->method('getLayoutHandle')
            ->willReturn($expectedLayoutHandle);

        $layoutHandle = $this->createBlockInstance('test-template.phtml', 'test-block-name');
        $result = $layoutHandle->getLayoutHandle();

        $this->assertSame($expectedLayoutHandle, $result);
    }

    /**
     * @param string $template
     * @param string $blockName
     * @return Block
     */
    private function createBlockInstance($template, $blockName)
    {
        return new Block($this->mockBlockRenderer, $template, $blockName, $this->testProjectionSourceData);
    }
}
