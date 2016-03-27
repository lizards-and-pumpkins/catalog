<?php

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\InvalidFileContentTypeException;
use LizardsAndPumpkins\Import\FileStorage\File;
use LizardsAndPumpkins\Import\FileStorage\FileContent;
use LizardsAndPumpkins\Import\FileStorage\Stub\CastableToStringStub;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\FileContent
 */
class FileContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $invalidStringContent
     * @param string $expectedType
     * @dataProvider invalidStringContentProvider
     */
    public function testItThrowsAnExceptionIfTheInputIsNotCastableToString($invalidStringContent, $expectedType)
    {
        $this->expectException(InvalidFileContentTypeException::class);
        $this->expectExceptionMessage(sprintf('Unable to cast file content to string, got "%s"', $expectedType));
        FileContent::fromString($invalidStringContent);
    }

    /**
     * @return array[]
     */
    public function invalidStringContentProvider()
    {
        return [
            [[], 'array'],
            [new \stdClass(), 'stdClass'],
        ];
    }

    /**
     * @param mixed $validStringContent
     * @dataProvider validStringContentProvider
     */
    public function testItReturnsAFileContentInstanceForStringableTypes($validStringContent)
    {
        $this->assertInstanceOf(FileContent::class, FileContent::fromString($validStringContent));
    }

    /**
     * @return array[]
     */
    public function validStringContentProvider()
    {
        return [
            ['test'],
            [123],
            [0.1],
            [null],
            [new CastableToStringStub()],
        ];
    }

    public function testItReturnsTheGivenContentAsAString()
    {
        $this->assertSame('test content', (string) FileContent::fromString('test content'));
    }

    public function testItReturnsAFileContentInstanceForFiles()
    {
        $testContent = 'file content';
        $mockFile = $this->getMock(File::class);
        $mockFile->method('getContent')->willReturn($testContent);
        
        $fileContent = FileContent::fromFile($mockFile);
        
        $this->assertInstanceOf(FileContent::class, $fileContent);
        $this->assertSame($testContent, (string) $fileContent);
    }
}
