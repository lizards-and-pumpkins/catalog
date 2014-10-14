<?php

namespace Brera\PoC;

/**
 * Class HttpUrlTest
 *
 * @package Brera\PoC
 * @covers  \Brera\PoC\HttpUrl
 * @covers  \Brera\PoC\Url
 */
class HttpTest extends UrlTest
{
    public function setUp()
    {
        $this->httpUrl = HttpUrl::fromString($this->urlString);
    }
}
