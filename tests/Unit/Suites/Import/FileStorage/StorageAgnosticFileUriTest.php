<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\InvalidFileIdentifierException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri
 */
class StorageAgnosticFileUriTest extends TestCase
{

    /**
     * @param mixed $invalidIdentifier
     * @param string $expectedType
     * @dataProvider invalidFileIdentifierProvider
     */
    public function testItThrowsAnExceptionIfTheFileIdentifierIsInvalid($invalidIdentifier, string $expectedType): void
    {
        $this->expectException(InvalidFileIdentifierException::class);
        $this->expectExceptionMessage(sprintf('The file identifier has to be a string, got "%s"', $expectedType));
        StorageAgnosticFileUri::fromString($invalidIdentifier);
    }

    /**
     * @return array[]
     */
    public function invalidFileIdentifierProvider() : array
    {
        return [
            [123, 'integer'],
            [null, 'NULL'],
        ];
    }

    /**
     * @dataProvider emptyFileIdentifierProvider
     */
    public function testItThrowsAnExceptionIfTheFileIdentifierStringIsEmpty(string $emptyIdentifier): void
    {
        $this->expectException(InvalidFileIdentifierException::class);
        $this->expectExceptionMessage('The file identifier must not be empty');
        StorageAgnosticFileUri::fromString($emptyIdentifier);
    }

    /**
     * @return array[]
     */
    public function emptyFileIdentifierProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testItReturnsAFileIdentifierInstance(): void
    {
        $fileIdentifierString = 'test';
        $this->assertInstanceOf(
            StorageAgnosticFileUri::class,
            StorageAgnosticFileUri::fromString($fileIdentifierString)
        );
    }

    /**
     * @dataProvider fileIdentifierStringProvider
     */
    public function testItReturnsTheFileIdentifierAsAString(string $identifierString): void
    {
        $this->assertEquals($identifierString, StorageAgnosticFileUri::fromString($identifierString));
    }

    /**
     * @return array[]
     */
    public function fileIdentifierStringProvider() : array
    {
        return [
            ['test1'],
            ['test2'],
        ];
    }

    public function testItAcceptsAFileIdentifierAsInput(): void
    {
        $sourceIdentifier = StorageAgnosticFileUri::fromString('test');
        $otherIdentifier = StorageAgnosticFileUri::fromString($sourceIdentifier);

        $this->assertInstanceOf(StorageAgnosticFileUri::class, $otherIdentifier);
        $this->assertSame('test', (string) $otherIdentifier);
    }
}
