<?php

namespace Brera\PoC;

/**
 * Class HttpUrlTest
 *
 * @package Brera\PoC
 * @covers  \Brera\PoC\HttpUrl
 * @covers  \Brera\PoC\Url
 */
class HttpUrlTest extends \PHPUnit_Framework_TestCase
{
    private $urlString = 'http://example.com/path';
    /**
     * @var HttpUrl
     */
    private $httpUrl;

    public function setUp()
    {
        $this->httpUrl = HttpUrl::fromString($this->urlString);
    }

    /**
     * @test
     */
    public function itShouldBeUnsecure()
    {
        $this->assertFalse($this->httpUrl->isProtocolEncrypted());
    }

    /**
     * @test
     */
    public function itShouldGivePath()
    {
        $this->assertEquals('/path', $this->httpUrl->getPath());
    }

    /**
     * @test
     */
    public function itShouldGiveTheUrlBack()
    {
        $this->assertEquals($this->urlString, (string)$this->httpUrl);
    }

    /**
     * @expectedException \Brera\PoC\UnknownProtocolException
     * @test
     */
    public function itShouldThrowExceptionForNonHttp()
    {
        $httpUrl = HttpUrl::fromString('ftp://user:pass@example.com');
    }
}
