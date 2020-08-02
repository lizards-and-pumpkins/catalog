<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\InvalidFileContentTypeException;
use LizardsAndPumpkins\Import\FileStorage\Stub\CastableToStringStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\FileContent
 */
class FileContentTest extends TestCase
{
    /**
     * @dataProvider invalidStringContentProvider
     * @param mixed $invalidStringContent
     * @param string $expectedType
     */
    public function testItThrowsAnExceptionIfTheInputIsNotCastableToString($invalidStringContent, string $expectedType): void
    {
        $this->expectException(InvalidFileContentTypeException::class);
        $this->expectExceptionMessage(sprintf('Unable to cast file content to string, got "%s"', $expectedType));
        FileContent::fromString($invalidStringContent);
    }

    /**
     * @return array[]
     */
    public function invalidStringContentProvider() : array
    {
        return [
            [[], 'array'],
            [new \stdClass(), 'stdClass'],
        ];
    }

    /**
     * @dataProvider validStringContentProvider
     * @param mixed $validStringContent
     */
    public function testItReturnsAFileContentInstanceForStringableTypes($validStringContent): void
    {
        $this->assertInstanceOf(FileContent::class, FileContent::fromString($validStringContent));
    }

    /**
     * @return array[]
     */
    public function validStringContentProvider() : array
    {
        return [
            ['test'],
            [123],
            [0.1],
            [null],
            [new CastableToStringStub()],
        ];
    }

    public function testItReturnsTheGivenContentAsAString(): void
    {
        $this->assertSame('test content', (string) FileContent::fromString('test content'));
    }

    public function testItReturnsAFileContentInstanceForFiles(): void
    {
        $testContent = 'file content';
        $mockFile = $this->createMock(File::class);
        $mockFile->method('getContent')->willReturn(FileContent::fromString($testContent));
        $mockFile->method('__toString')->willReturn($testContent);
        
        $fileContent = FileContent::fromFile($mockFile);
        
        $this->assertInstanceOf(FileContent::class, $fileContent);
        $this->assertSame($testContent, (string) $fileContent);
    }
}
