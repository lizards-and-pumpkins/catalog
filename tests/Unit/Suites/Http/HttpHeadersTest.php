<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\HeaderNotPresentException;
use LizardsAndPumpkins\Http\Exception\InvalidHttpHeadersException;

/**
 * @covers \LizardsAndPumpkins\Http\HttpHeaders
 */
class HttpHeadersTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsAnExceptionIfAnInvalidHeaderIsRequested()
    {
        $this->expectException(HeaderNotPresentException::class);
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
        $this->assertTrue(HttpHeaders::fromArray(['A-http-header' => 'the-header-value'])->has('a-HTTP-header'));
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

    public function testEmptyArrayIsReturnedInCaseNoHeadersWereSet()
    {
        $headers = HttpHeaders::fromArray([]);
        $this->assertEquals([], $headers->getAll());
    }

    public function testAllHeadersAreReturned()
    {
        $headersArray = ['header 1 name' => 'header 1 value', 'header 2 name' => 'header 2 value'];
        $headers = HttpHeaders::fromArray($headersArray);

        $this->assertEquals($headersArray, $headers->getAll());
    }

    /**
     * @dataProvider getMalformedHeadersSources
     * @param mixed[] $malformedHeadersSource
     */
    public function testExceptionIsThrownDuringAttemptToCreateHeadersFromArrayContainingNonStringKeysOrValues(
        array $malformedHeadersSource
    ) {
        $this->expectException(InvalidHttpHeadersException::class);
        HttpHeaders::fromArray($malformedHeadersSource);
    }

    /**
     * @return array[]
     */
    public function getMalformedHeadersSources() : array
    {
        return [
            [['foo' => 1]],
            [['bar']],
            [[1 => []]]
        ];
    }
}
