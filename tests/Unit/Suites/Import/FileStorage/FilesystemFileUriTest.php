<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\InvalidFileURIException;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\FilesystemFileUri
 */
class FilesystemFileUriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $notStringURI
     * @param string $expectedType
     * @dataProvider invalidNonStringFileUriProvider
     */
    public function testItThrowsAnExceptionIfTheFileUriIsNotAString($notStringURI, string $expectedType)
    {
        $this->expectException(InvalidFileURIException::class);
        $this->expectExceptionMessage(sprintf('The file URI has to be a string, got "%s"', $expectedType));
        FilesystemFileUri::fromString($notStringURI);
    }

    /**
     * @return array[]
     */
    public function invalidNonStringFileUriProvider() : array
    {
        return [
            [null, 'NULL'],
            [123, 'integer'],
        ];
    }

    /**
     * @dataProvider emptyFileUriProvider
     */
    public function testItThrowsAnExceptionIfTheUriIsEmpty(string $emptyURI)
    {
        $this->expectException(InvalidFileURIException::class);
        $this->expectExceptionMessage('The file URI must not be an empty string');
        FilesystemFileUri::fromString($emptyURI);
    }

    /**
     * @return array[]
     */
    public function emptyFileUriProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testItReturnsAFilesystemFileUri()
    {
        $filesystemFileURI = FilesystemFileUri::fromString('/test');
        
        $this->assertInstanceOf(FilesystemFileUri::class, $filesystemFileURI);
        $this->assertInstanceOf(StorageSpecificFileUri::class, $filesystemFileURI);
    }

    public function testItReturnsTheURIString()
    {
        $this->assertSame('test', (string) FilesystemFileUri::fromString('test'));
    }

    public function testItReturnsAFileURIWhenGivenAFilesystemFileInstance()
    {
        $fileURIString = 'test';
        $sourceFilesystemFileURI = FilesystemFileUri::fromString($fileURIString);
        $newFilesystemFileURI = FilesystemFileUri::fromString($sourceFilesystemFileURI);
        
        $this->assertSame($fileURIString, (string) $newFilesystemFileURI);
    }
}
