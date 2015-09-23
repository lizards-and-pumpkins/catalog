<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 */
class SearchDocumentFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testDocumentFieldKeyAndValueAreSetAndReturned()
    {
        $key = 'foo';
        $value = 'bar';

        $searchDocumentField = SearchDocumentField::fromKeyAndValue($key, $value);

        $this->assertEquals($key, $searchDocumentField->getKey());
        $this->assertEquals($value, $searchDocumentField->getValue());
    }

    /**
     * @param mixed $invalidKey
     * @dataProvider invalidKeyProvider
     */
    public function testExceptionIsThrownIfInvalidKeyIsSpecified($invalidKey)
    {
        $this->setExpectedException(InvalidSearchDocumentFieldKeyException::class);
        SearchDocumentField::fromKeyAndValue($invalidKey, 'foo');
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
}
