<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpsUrl
 * @covers \Brera\Http\HttpUrl
 * @uses \Brera\Http\HttpUrl
 */
class HttpsUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testProtocolIsEncrypted()
    {
        $httpsUrl = HttpUrl::fromString('https://example.com/path');

        $this->assertInstanceOf(HttpsUrl::class, $httpsUrl);
        $this->assertTrue($httpsUrl->isProtocolEncrypted());
    }
}
