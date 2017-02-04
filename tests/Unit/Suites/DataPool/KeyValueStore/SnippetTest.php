<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyValueStore;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\InvalidKeyException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class SnippetTest extends TestCase
{
    public function testExceptionIsThrownOnNonStringKey()
    {
        $this->expectException(\TypeError::class);

        $content = 'doesn\'t matter';
        Snippet::create(1, $content);
    }

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
     * @return array[]
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
