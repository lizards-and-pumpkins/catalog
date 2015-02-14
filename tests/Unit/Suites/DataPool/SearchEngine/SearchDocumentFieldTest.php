<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocumentField
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

        $searchDocumentField = new searchDocumentField($key, $value);

        $this->assertEquals($key, $searchDocumentField->getKey());
        $this->assertEquals($value, $searchDocumentField->getValue());
    }

    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\InvalidSearchDocumentFieldKeyException
     * @param $invalidKey
     * @dataProvider invalidKeyProvider
     */
    public function itShouldThrowAnExceptionIfInvalidKeyIsSpecified($invalidKey)
    {
        new SearchDocumentField($invalidKey, 'foo');
    }

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
