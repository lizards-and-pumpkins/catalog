<?php


namespace Brera\Http;

/**
 * @covers Brera\Http\HttpHeaders
 */
class HttpHeadersTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsAnExceptionIfAnInvalidHeaderIsRequested()
    {
        $this->setExpectedException(\Brera\Http\HeaderNotPresentException::class);
        HttpHeaders::fromArray([])->get('a-http-request-header');
    }

    public function testItReturnsFalseIfTheRequestedHeaderIsNotPresent()
    {
        $this->assertFalse(HttpHeaders::fromArray([])->has('not-present-header'));
    }

    public function testItReturnsTrueIfTheRequestHeaderIsPresent()
    {
        $headerName = 'a-http-header';
        $this->assertTrue(HttpHeaders::fromArray([$headerName => 'the-header-value'])->has($headerName));
    }

    public function testItChecksForHeaderPresenceInACaseInsensitiveManner()
    {
        $headerName = 'a-http-header';
        $this->assertTrue(HttpHeaders::fromArray([$headerName => 'the-header-value'])->has(strtoupper($headerName)));
    }

    public function testItReturnsTheHeaderIfPresent()
    {
        $headerName = 'a-http-header';
        $headerValue = 'the-header-value';
        $this->assertSame($headerValue, HttpHeaders::fromArray([$headerName => $headerValue])->get($headerName));
    }

    public function testItReturnsTheHeaderValueUsingTheHeaderNameInACaseInsensitiveManner()
    {
        $headerName = 'a-http-header';
        $headerValue = 'the-header-value';
        $headers = HttpHeaders::fromArray([$headerName => $headerValue]);
        $this->assertSame($headerValue, $headers->get(strtoupper($headerName)));
    }
}
