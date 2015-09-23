<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\Exception\InvalidSearchDocumentFieldKeyException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\Exception\InvalidSearchDocumentFieldValueException;


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
        $this->setExpectedException(InvalidSearchDocumentFieldKeyException::class);
        SearchDocumentField::fromKeyAndValues($invalidKey, ['foo']);
    }

    /**
     * @return mixed[]
     */
    public function invalidKeyProvider()
    {
        return [
            [''],
            [' '],
            ['.foo'],
            ['1'],
            ['-'],
            [111],
            ['1foo'],
            [null],
            [[]],
            [new \stdClass()],
            [true],
            [false],
        ];
    }

    public function testItThrowsAnExceptionIfTheValuesContainNonScalars()
    {
        $this->setExpectedException(
            InvalidSearchDocumentFieldValueException::class,
            'Only string, integer, float and boolean attribute values are allowed, got "array"'
        );
        SearchDocumentField::fromKeyAndValues('foo', [[]]);
    }
}
