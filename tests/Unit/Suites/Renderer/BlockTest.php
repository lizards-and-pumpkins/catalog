<?php

namespace Brera\Renderer;

use Brera\ProjectionSourceData;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Renderer\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var ProjectionSourceData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataObject;

    /**
     * @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockBlockRenderer;
    
    public function setUp()
    {
        $this->mockBlockRenderer = $this->getMockBuilder(BlockRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLayoutHandle', 'getChildBlockOutput'])
            ->getMock();
        $this->stubDataObject = $this->getMock(ProjectionSourceData::class);
    }
    
    /**
     * @param string $template
     * @param string $blockName
     * @return Block
     */
    private function createBlockInstance($template, $blockName)
    {
        return new Block($this->mockBlockRenderer, $template, $blockName, $this->stubDataObject);
    }

    /**
     * @test
     */
    public function itShouldReturnTheBlocksName()
    {
        $blockName = 'test-block-name';
        $instance = $this->createBlockInstance('test-template.phtml', $blockName);
        $this->assertEquals($blockName, $instance->getBlockName());
    }

    /**
     * @test
     */
    public function itShouldReturnTheDataObject()
    {
        $block = $this->createBlockInstance('test-template.phtml', 'test-block-name');
        $method = new \ReflectionMethod($block, 'getDataObject');
        $method->setAccessible(true);
        $this->assertSame($this->stubDataObject, $method->invoke($block));
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\TemplateFileNotReadableException
     */
    public function itShouldThrowAnExceptionIfTemplateFileDoesNotExist()
    {
        $block = $this->createBlockInstance('test-template.phtml', 'test-block-name');
        $block->render();
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\TemplateFileNotReadableException
     */
    public function itShouldThrowAnExceptionIfTemplateFileIsNotReadable()
    {
        $templateFilePath = $this->getUniqueTempDir() . '/test-template.phtml';
        
        $this->createFixtureFile($templateFilePath, '', 0000);

        $block = $this->createBlockInstance($templateFilePath, 'test-block-name');
        $block->render();
    }

    /**
     * @test
     */
    public function itShouldReturnSameStringAsTranslation()
    {
        $block = $this->createBlockInstance('test-template.phtml', 'test-block-name');
        $result = $block->__('foo');

        $this->assertEquals('foo', $result);
    }

    /**
     * @test
     */
    public function itShouldRenderTheTemplate()
    {
        $template = $this->getUniqueTempDir() . '/test-template.phtml';
        $templateContent = 'The template content';
        $this->createFixtureFile($template, $templateContent);
        $block = $this->createBlockInstance($template, $templateContent);
        $this->assertEquals($templateContent, $block->render());
    }

    /**
     * @test
     */
    public function itShouldDelegateToTheBlockRendererToGetChildBlockOutput()
    {
        $blockName = 'test-block-name';
        $childName = 'child-name';
        $this->mockBlockRenderer->expects($this->once())
            ->method('getChildBlockOutput')
            ->with($blockName, $childName);
        $block = $this->createBlockInstance('template.phtml', $blockName);
        $block->getChildOutput($childName);
    }
}
