<?php

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\InvalidFileIdentifierException;
use LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri
 */
class StorageAgnosticFileUriTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param mixed $invalidIdentifier
     * @param string $expectedType
     * @dataProvider invalidFileIdentifierProvider
     */
    public function testItThrowsAnExceptionIfTheFileIdentifierIsInvalid($invalidIdentifier, $expectedType)
    {
        $this->expectException(InvalidFileIdentifierException::class);
        $this->expectExceptionMessage(sprintf('The file identifier has to be a string, got "%s"', $expectedType));
        StorageAgnosticFileUri::fromString($invalidIdentifier);
    }

    /**
     * @return array[]
     */
    public function invalidFileIdentifierProvider()
    {
        return [
            [123, 'integer'],
            [null, 'NULL'],
        ];
    }

    /**
     * @param string $emptyIdentifier
     * @dataProvider emptyFileIdentifierProvider
     */
    public function testItThrowsAnExceptionIfTheFileIdentifierStringIsEmpty($emptyIdentifier)
    {
        $this->expectException(InvalidFileIdentifierException::class);
        $this->expectExceptionMessage('The file identifier must not be empty');
        StorageAgnosticFileUri::fromString($emptyIdentifier);
    }

    /**
     * @return array[]
     */
    public function emptyFileIdentifierProvider()
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testItReturnsAFileIdentifierInstance()
    {
        $fileIdentifierString = 'test';
        $this->assertInstanceOf(
            StorageAgnosticFileUri::class,
            StorageAgnosticFileUri::fromString($fileIdentifierString)
        );
    }

    /**
     * @param string $identifierString
     * @dataProvider fileIdentifierStringProvider
     */
    public function testItReturnsTheFileIdentifierAsAString($identifierString)
    {
        $this->assertEquals($identifierString, StorageAgnosticFileUri::fromString($identifierString));
    }

    /**
     * @return array[]
     */
    public function fileIdentifierStringProvider()
    {
        return [
            ['test1'],
            ['test2'],
        ];
    }

    public function testItAcceptsAFileIdentifierAsInput()
    {
        $sourceIdentifier = StorageAgnosticFileUri::fromString('test');
        $otherIdentifier = StorageAgnosticFileUri::fromString($sourceIdentifier);

        $this->assertInstanceOf(StorageAgnosticFileUri::class, $otherIdentifier);
        $this->assertSame('test', (string) $otherIdentifier);
    }
}
