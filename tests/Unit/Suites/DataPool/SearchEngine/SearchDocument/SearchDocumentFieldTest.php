<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidSearchDocumentFieldKeyException;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidSearchDocumentFieldValueException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 */
class SearchDocumentFieldTest extends TestCase
{
    public function testDocumentFieldKeyAndValueAreSetAndReturned(): void
    {
        $key = 'foo';
        $values = ['bar'];

        $searchDocumentField = SearchDocumentField::fromKeyAndValues($key, $values);

        $this->assertEquals($key, $searchDocumentField->getKey());
        $this->assertEquals($values, $searchDocumentField->getValues());
    }

    /**
     * @param mixed $invalidKey
     * @dataProvider invalidKeyProvider
     */
    public function testExceptionIsThrownIfInvalidKeyIsSpecified($invalidKey): void
    {
        $this->expectException(InvalidSearchDocumentFieldKeyException::class);
        SearchDocumentField::fromKeyAndValues($invalidKey, ['foo']);
    }

    /**
     * @return mixed[]
     */
    public function invalidKeyProvider() : array
    {
        return [
            [''],
            [' '],
            ['.foo'],
            ['1'],
            ['-'],
            ['1foo'],
        ];
    }

    public function testExceptionIsThrownIfInvalidKeyTypeIsSpecified(): void
    {
        $this->expectException(\TypeError::class);
        SearchDocumentField::fromKeyAndValues(1, ['foo']);
    }

    public function testItThrowsAnExceptionIfTheValuesContainNonScalars(): void
    {
        $this->expectException(InvalidSearchDocumentFieldValueException::class);
        $this->expectExceptionMessage(
            'Only string, integer, float and boolean attribute values are allowed, got "array"'
        );
        SearchDocumentField::fromKeyAndValues('foo', [[]]);
    }
}
