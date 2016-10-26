<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidSearchDocumentFieldKeyException;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidSearchDocumentFieldValueException;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 */
class SearchDocumentFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testDocumentFieldKeyAndValueAreSetAndReturned()
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
    public function testExceptionIsThrownIfInvalidKeyIsSpecified($invalidKey)
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

    public function testExceptionIsThrownIfInvalidKeyTypeIsSpecified()
    {
        $this->expectException(\TypeError::class);
        SearchDocumentField::fromKeyAndValues(1, ['foo']);
    }

    public function testItThrowsAnExceptionIfTheValuesContainNonScalars()
    {
        $this->expectException(InvalidSearchDocumentFieldValueException::class);
        $this->expectExceptionMessage(
            'Only string, integer, float and boolean attribute values are allowed, got "array"'
        );
        SearchDocumentField::fromKeyAndValues('foo', [[]]);
    }
}
