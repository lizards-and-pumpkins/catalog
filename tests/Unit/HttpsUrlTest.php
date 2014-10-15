<?php

namespace Brera\PoC;

/**
 * Class HttpsUrlTest
 *
 * @package Brera\PoC
 * @covers  \Brera\PoC\HttpsUrl
 */
class HttpsUrlTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function itShouldBeEncrypted()
    {
        $this->assertTrue(HttpUrl::fromString('https://example.com/path')->isProtocolEncrypted());
    }
}
