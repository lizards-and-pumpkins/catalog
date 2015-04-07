<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 */
class SearchDocumentFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldSetAndGetDocumentFieldKeyAndValue()
    {
        $key = 'foo';
        $value = 'bar';

        $searchDocumentField = SearchDocumentField::fromKeyAndValue($key, $value);

        $this->assertEquals($key, $searchDocumentField->getKey());
        $this->assertEquals($value, $searchDocumentField->getValue());
    }

    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\SearchDocument\InvalidSearchDocumentFieldKeyException
     * @param mixed $invalidKey
     * @dataProvider invalidKeyProvider
     */
    public function itShouldThrowAnExceptionIfInvalidKeyIsSpecified($invalidKey)
    {
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
