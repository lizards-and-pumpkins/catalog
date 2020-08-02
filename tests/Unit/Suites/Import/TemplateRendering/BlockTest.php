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
     * @var BlockRenderer|MockObject
     */
    private $mockBlockRenderer;

    /**
     * @var Block
     */
    private $block;

    final protected function setUp(): void
    {
        $this->testTemplateFilePath = $this->getUniqueTempDir() . '/test-template.phtml';
        $this->mockBlockRenderer = $this->createMock(BlockRenderer::class);

        $this->block = new Block(
            $this->mockBlockRenderer,
            $this->testTemplateFilePath,
            $this->testBlockName,
            $this->testProjectionSourceData
        );
    }

    public function testBlocksNameIsReturned(): void
    {
        $this->assertEquals($this->testBlockName, $this->block->getBlockName());
    }

    public function testDataObjectIsReturned(): void
    {
        $method = new \ReflectionMethod($this->block, 'getDataObject');
        $method->setAccessible(true);

        $this->assertSame($this->testProjectionSourceData, $method->invoke($this->block));
    }

    public function testExceptionIsThrownIfTemplateFileDoesNotExist(): void
    {
        $this->expectException(TemplateFileNotReadableException::class);
        $this->block->render();
    }

    public function testExceptionIsThrownIfTemplateFileIsNotReadable(): void
    {
        $this->expectException(TemplateFileNotReadableException::class);
        $this->createFixtureFile($this->testTemplateFilePath, '', 0000);
        $this->block->render();
    }

    public function testTemplateIsReturned(): void
    {
        $templateContent = 'The template content';
        $this->createFixtureFile($this->testTemplateFilePath, $templateContent);

        $this->assertEquals($templateContent, $this->block->render());
    }

    public function testGettingChildBlockOutputIsDelegatedToBlockRenderer(): void
    {
        $childName = 'child-name';
        $this->mockBlockRenderer->expects($this->once())->method('getChildBlockOutput')
            ->with($this->testBlockName, $childName);

        $this->block->getChildOutput($childName);
    }

    public function testGettingLayoutHandleIsDelegatedToBlockRenderer(): void
    {
        $expectedLayoutHandle = 'foo';
        $this->mockBlockRenderer->method('getLayoutHandle')->willReturn($expectedLayoutHandle);

        $this->assertSame($expectedLayoutHandle, $this->block->getLayoutHandle());
    }

    public function testTranslationIsDelegatedToBlockRenderer(): void
    {
        $testSourceString = 'foo';
        $testTranslatedString = 'bar';
        $this->mockBlockRenderer->method('translate')->with($testSourceString)->willReturn($testTranslatedString);

        $this->assertEquals($testTranslatedString, $this->block->__($testSourceString));
    }

    public function testItDelegatesFetchingTheBaseUrlToTheBlockRenderer(): void
    {
        $baseUrl = new HttpBaseUrl('http://example.com/');
        $this->mockBlockRenderer->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        
        $this->assertSame($baseUrl, $this->block->getBaseUrl());
    }

    public function testDelegatesFetchingTheAssetsBaseUrlToTheBlockRenderer(): void
    {
        $assetsBaseUrl = new HttpBaseUrl('http://example.com/');
        $this->mockBlockRenderer->expects($this->once())->method('getAssetsBaseUrl')->willReturn($assetsBaseUrl);
        $this->assertSame($assetsBaseUrl, $this->block->getAssetsBaseUrl());
    }

    public function testFetchingWebsiteCodeIsDelegatedToBlockRenderer(): void
    {
        $dummyWebsiteCode = 'foo';
        $this->mockBlockRenderer->expects($this->once())->method('getWebsiteCode')->willReturn($dummyWebsiteCode);
        
        $this->assertSame($dummyWebsiteCode, $this->block->getWebsiteCode());
    }
}
