<?php

namespace LizardsAndPumpkins\Renderer;

use LizardsAndPumpkins\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Renderer\Exception\TemplateFileNotReadableException;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Renderer\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var string
     */
    private $testBlockName = 'test-block-name';

    /**
     * @var string
     */
    private $testTemplateFilePath;

    /**
     * @var mixed
     */
    private $testProjectionSourceData = 'test-projection-source-data';

    /**
     * @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockBlockRenderer;

    /**
     * @var Block
     */
    private $block;

    public function setUp()
    {
        $this->testTemplateFilePath = $this->getUniqueTempDir() . '/test-template.phtml';
        $this->mockBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);

        $this->block = new Block(
            $this->mockBlockRenderer,
            $this->testTemplateFilePath,
            $this->testBlockName,
            $this->testProjectionSourceData
        );
    }

    public function testBlocksNameIsReturned()
    {
        $this->assertEquals($this->testBlockName, $this->block->getBlockName());
    }

    public function testDataObjectIsReturned()
    {
        $method = new \ReflectionMethod($this->block, 'getDataObject');
        $method->setAccessible(true);

        $this->assertSame($this->testProjectionSourceData, $method->invoke($this->block));
    }

    public function testExceptionIsThrownIfTemplateFileDoesNotExist()
    {
        $this->setExpectedException(TemplateFileNotReadableException::class);
        $this->block->render();
    }

    public function testExceptionIsThrownIfTemplateFileIsNotReadable()
    {
        $this->setExpectedException(TemplateFileNotReadableException::class);
        $this->createFixtureFile($this->testTemplateFilePath, '', 0000);
        $this->block->render();
    }

    public function testTemplateIsReturned()
    {
        $templateContent = 'The template content';
        $this->createFixtureFile($this->testTemplateFilePath, $templateContent);

        $this->assertEquals($templateContent, $this->block->render());
    }

    public function testGettingChildBlockOutputIsDelegatedToBlockRenderer()
    {
        $childName = 'child-name';
        $this->mockBlockRenderer->expects($this->once())->method('getChildBlockOutput')
            ->with($this->testBlockName, $childName);

        $this->block->getChildOutput($childName);
    }

    public function testGettingLayoutHandleIsDelegatedToBlockRenderer()
    {
        $expectedLayoutHandle = 'foo';
        $this->mockBlockRenderer->method('getLayoutHandle')->willReturn($expectedLayoutHandle);

        $this->assertSame($expectedLayoutHandle, $this->block->getLayoutHandle());
    }

    public function testTranslationIsDelegatedToBlockRenderer()
    {
        $testSourceString = 'foo';
        $testTranslatedString = 'bar';
        $this->mockBlockRenderer->method('translate')->with($testSourceString)->willReturn($testTranslatedString);

        $this->assertEquals($testTranslatedString, $this->block->__($testSourceString));
    }

    public function testItDelegatesFetchingTheBaseUrlToTheBlockRenderer()
    {
        $dummyBaseUrl = 'dummy base url';
        $this->mockBlockRenderer->expects($this->once())->method('getBaseUrl')->willReturn($dummyBaseUrl);
        $this->assertSame($dummyBaseUrl, $this->block->getBaseUrl());
    }
}
