<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpsUrl
 * @covers \Brera\Http\HttpUrl
 * @uses \Brera\Http\HttpUrl
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
