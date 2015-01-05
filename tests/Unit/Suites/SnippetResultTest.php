<?php

namespace Brera;

class SnippetResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $invalidKey
     *
     * @test
     * @dataProvider invalidKeyProvider
     * @expectedException \Brera\InvalidKeyException
     */
    public function itShouldThrowOnInvalidKey($invalidKey)
    {
        $content = 'doesn\'t matter';
        SnippetResult::create($invalidKey, $content);
    }

    public function invalidKeyProvider()
    {
        return [
            [null],
            [''],
            [123],
            [new \stdClass()],
            [array()],
            ['äöü'],
            ['%'],
            ['$']
        ];
    }

    /**
     * @param string $validKey
     *
     * @test
     * @dataProvider validKeyProvider
     */
    public function itShouldAllowValidKey($validKey)
    {
        $content = 'doesn\'t matter';
        $result = SnippetResult::create($validKey, $content);
        $this->assertInstanceOf(SnippetResult::class, $result);
    }

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
        $result = SnippetResult::create($key, $content);

        $this->assertEquals($content, $result->getContent());
        $this->assertEquals($key, $result->getKey());
    }
}
