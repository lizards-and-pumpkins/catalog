<?php

namespace Brera\PoC\Http;

/**
 * Class HttpUrlTest
 *
 * @package \Brera\PoC
 * @covers  \Brera\PoC\Http\HttpUrl
 */
class HttpUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpUrl
     */
    private $url;

    /**
     * @var string
     */
    private $urlString = 'http://example.com/path';

    public function setUp()
    {
        $this->url = HttpUrl::fromString($this->urlString);
    }

    /**
     * @test
     */
    public function itShouldReturnPath()
    {
        $this->assertEquals('/path', $this->url->getPath());
    }

    /**
     * @test
     */
    public function itShouldReturnSlashAsPathIfNoPathIsGiven()
    {
        $url = HttpUrl::fromString('http://example.com');
        $this->assertEquals('/', $url->getPath());
    }

    /**
     * @test
     */
    public function itShouldReturnSlashAsPathIfSlashPathIsGiven()
    {
        $url = HttpUrl::fromString('http://example.com/');
        $this->assertEquals('/', $url->getPath());
    }

    /**
     * @test
     */
    public function itShouldGiveTheUrlBack()
    {
        $this->assertEquals($this->urlString, (string)$this->url);
    }

    /**
     * @expectedException \Brera\PoC\Http\UnknownProtocolException
     * @test
     */
    public function itShouldThrowExceptionForNonHttp()
    {
        HttpUrl::fromString('ftp://user:pass@example.com');
    }

    /**
     * @test
     */
    public function itShouldNotBeEncrypted()
    {
        $this->assertFalse($this->url->isProtocolEncrypted());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowExceptionWhenNoUrlIsPassed()
    {
        HttpUrl::fromString('this is not a valid url');
    }
}
