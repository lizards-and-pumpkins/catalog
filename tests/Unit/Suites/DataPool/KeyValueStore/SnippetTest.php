<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyValueStore;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\InvalidKeyException;

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
     * @return array[]
     */
    public function invalidKeyProvider() : array
    {
        return [
            [''],
            ['äöü'],
            ['%'],
            ['$']
        ];
    }

    /**
     * @dataProvider validKeyProvider
     */
    public function testSnippetIsCreatedIfValidKeyIsProvided(string $validKey)
    {
        $content = 'doesn\'t matter';
        $result = Snippet::create($validKey, $content);
        $this->assertInstanceOf(Snippet::class, $result);
    }

    /**
     * @return string[]
     */
    public function validKeyProvider() : array
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
