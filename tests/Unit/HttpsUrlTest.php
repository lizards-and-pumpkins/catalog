<?php

namespace Brera\PoC;

/**
 * Class HttpsUrlTest
 *
 * @package Brera\PoC
 * @covers  \Brera\PoC\HttpsUrl
 * @uses    \Brera\PoC\HttpUrl
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
