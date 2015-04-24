<?php

namespace Brera;

/**
 * @covers \Brera\Snippet
 */
class SnippetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Brera\InvalidKeyException
     * @param mixed $invalidKey
     * @dataProvider invalidKeyProvider
     */
    public function itShouldThrowOnInvalidKey($invalidKey)
    {
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
     * @test
     * @param string $validKey
     * @dataProvider validKeyProvider
     */
    public function itShouldAllowValidKey($validKey)
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
        ];
    }

    /**
     * @test
     */
    public function shouldReturnKeyAndContent()
    {
        $content = 'doesn\'t matter';
        $key = 'key';
        $result = Snippet::create($key, $content);

        $this->assertEquals($content, $result->getContent());
        $this->assertEquals($key, $result->getKey());
    }
}
