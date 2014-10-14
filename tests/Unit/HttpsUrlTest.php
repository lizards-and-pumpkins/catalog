<?php

namespace Brera\PoC;

/**
 * Class HttpsUrlTest
 *
 * @package Brera\PoC
 * @covers  \Brera\PoC\HttpsUrl
 * @covers  \Brera\PoC\Url
 */
class HttpsUrlTest extends UrlTest
{
    protected $urlString = 'https://example.com/path';

    public function setUp()
    {
        $this->httpUrl = HttpUrl::fromString($this->urlString);
    }

    /**
     * @test
     */
    public function checkSecurity()
    {
        $this->assertTrue($this->httpUrl->isProtocolEncrypted());
    }
}
