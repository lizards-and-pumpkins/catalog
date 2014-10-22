<?php

namespace Brera\PoC\Http;

/**
 * @covers \Brera\PoC\Http\HttpsUrl
 * @covers \Brera\PoC\Http\HttpUrl
 * @uses \Brera\PoC\Http\HttpUrl
 */
class HttpsUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldBeEncrypted()
    {
        $httpsUrl = HttpUrl::fromString('https://example.com/path');

        $this->assertInstanceOf(HttpsUrl::class, $httpsUrl);
        $this->assertTrue($httpsUrl->isProtocolEncrypted());
    }
}
