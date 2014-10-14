<?php

namespace Brera\PoC;

/**
 * Class HttpUrlTest
 *
 * @package Brera\PoC
 * @covers  \Brera\PoC\HttpUrl
 * @covers  \Brera\PoC\Url
 */
abstract class UrlTest extends \PHPUnit_Framework_TestCase
{
    protected $urlString = 'http://example.com/path';

    /**
     * @var HttpUrl
     */
    protected $httpUrl;

    /**
     * @test
     */
    public function checkSecurity()
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
