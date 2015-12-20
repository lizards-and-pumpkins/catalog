<?php

namespace LizardsAndPumpkins\Utils\FileStorage;

use LizardsAndPumpkins\Utils\FileStorage\Exception\InvalidFileURIException;

/**
 * @covers \LizardsAndPumpkins\Utils\FileStorage\FilesystemFileUri
 */
class FilesystemFileUriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $notStringURI
     * @dataProvider invalidNonStringFileUriProvider
     */
    public function testItThrowsAnExceptionIfTheFileUriIsNotAString($notStringURI, $expectedType)
    {
        $this->setExpectedException(
            InvalidFileURIException::class,
            sprintf('The file URI has to be a string, got "%s"', $expectedType)
        );
        FilesystemFileUri::fromString($notStringURI);
    }

    /**
     * @return array[]
     */
    public function invalidNonStringFileUriProvider()
    {
        return [
            [null, 'NULL'],
            [123, 'integer'],
        ];
    }

    /**
     * @param string $emptyURI
     * @dataProvider emptyFileUriProvider
     */
    public function testItThrowsAnExceptionIfTheUriIsEmpty($emptyURI)
    {
        $this->setExpectedException(
            InvalidFileURIException::class,
            'The file URI must not be an empty string'
        );
        FilesystemFileUri::fromString($emptyURI);
    }

    /**
     * @return array[]
     */
    public function emptyFileUriProvider()
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
        $newFilesysteFileURI = FilesystemFileUri::fromString($sourceFilesystemFileURI);
        
        $this->assertSame($fileURIString, (string) $newFilesysteFileURI);
    }
}
