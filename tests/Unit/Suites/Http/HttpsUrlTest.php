<?php

namespace LizardsAndPumpkins\Http;

/**
 * @covers \LizardsAndPumpkins\Http\HttpsUrl
 * @covers \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
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
