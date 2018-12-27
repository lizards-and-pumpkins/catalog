<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl;
use LizardsAndPumpkins\Import\RootTemplate\Import\Exception\TemplateFileNotReadableException;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\Block
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 */
class BlockTest extends TestCase
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

    /**
     * @var Template
     */
    private $templateFileMock;

    public function setUp()
    {
        $this->testTemplateFilePath = $this->getUniqueTempDir() . '/test-template.phtml';

        $this->templateFileMock = $this->createMock(Template::class);
        $this->templateFileMock->method('__toString')->willReturn($this->testTemplateFilePath);

        $this->mockBlockRenderer = $this->createMock(BlockRenderer::class);

        $this->block = new Block(
            $this->mockBlockRenderer,
            $this->templateFileMock,
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
        $this->expectException(TemplateFileNotReadableException::class);
        $this->block->render();
    }

    public function testExceptionIsThrownIfTemplateFileIsNotReadable()
    {
        $this->expectException(TemplateFileNotReadableException::class);
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
        $testSourceString     = 'foo';
        $testTranslatedString = 'bar';
        $this->mockBlockRenderer->method('translate')->with($testSourceString)->willReturn($testTranslatedString);

        $this->assertEquals($testTranslatedString, $this->block->__($testSourceString));
    }

    public function testItDelegatesFetchingTheBaseUrlToTheBlockRenderer()
    {
        $baseUrl = new HttpBaseUrl('http://example.com/');
        $this->mockBlockRenderer->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);

        $this->assertSame($baseUrl, $this->block->getBaseUrl());
    }

    public function testDelegatesFetchingTheAssetsBaseUrlToTheBlockRenderer()
    {
        $assetsBaseUrl = new HttpBaseUrl('http://example.com/');
        $this->mockBlockRenderer->expects($this->once())->method('getAssetsBaseUrl')->willReturn($assetsBaseUrl);
        $this->assertSame($assetsBaseUrl, $this->block->getAssetsBaseUrl());
    }

    public function testFetchingWebsiteCodeIsDelegatedToBlockRenderer()
    {
        $dummyWebsiteCode = 'foo';
        $this->mockBlockRenderer->expects($this->once())->method('getWebsiteCode')->willReturn($dummyWebsiteCode);

        $this->assertSame($dummyWebsiteCode, $this->block->getWebsiteCode());
    }
}
