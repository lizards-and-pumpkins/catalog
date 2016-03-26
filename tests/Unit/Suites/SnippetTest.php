<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\InvalidKeyException;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class SnippetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidKeyProvider
     * @param mixed $invalidKey
     */
    public function testExceptionIsThrownOnInvalidKey($invalidKey)
    {
        $this->expectException(InvalidKeyException::class);

        $content = 'doesn\'t matter';
        Snippet::create($invalidKey, $content);
    }

    /**
     * @return mixed[]
     */
    public function invalidKeyProvider()
    {
        return [
            [null],
            [''],
            [123],
            [new \stdClass()],
            [[]],
            ['äöü'],
            ['%'],
            ['$']
        ];
    }

    /**
     * @param string $validKey
     * @dataProvider validKeyProvider
     */
    public function testSnippetIsCreatedIfValidKeyIsProvided($validKey)
    {
        $content = 'doesn\'t matter';
        $result = Snippet::create($validKey, $content);
        $this->assertInstanceOf(Snippet::class, $result);
    }

    /**
     * @return string[]
     */
    public function validKeyProvider()
    {
        return [
            ['abcdef'],
            ['-_-'],
            ['a'],
            ['1'],
            ['foo.bar'],
            ['foo/bar'],
        ];
    }

    public function testSnippetKeyAndContentAreReturned()
    {
        $content = 'doesn\'t matter';
        $key = 'key';
        $result = Snippet::create($key, $content);

        $this->assertEquals($content, $result->getContent());
        $this->assertEquals($key, $result->getKey());
    }
}
