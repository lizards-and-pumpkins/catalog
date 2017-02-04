<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\HeaderNotPresentException;
use LizardsAndPumpkins\Http\Exception\InvalidHttpHeadersException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\HttpHeaders
 */
class HttpHeadersTest extends TestCase
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

    public function testHeadersCanBeCreatedFromGlobals()
    {
        $this->assertInstanceOf(HttpHeaders::class, HttpHeaders::fromGlobalRequestHeaders());
    }

    public function testHeadersContainGlobalValues()
    {
        $dummyValue = 'bar';
        $_SERVER['HTTP_FOO'] = $dummyValue;
        
        $result = HttpHeaders::fromGlobalRequestHeaders();

        unset($_SERVER['HTTP_FOO']);

        $this->assertSame(['foo' => $dummyValue], $result->getAll());
    }
    
    public function testOnlyHttpGlobalsAreUsedForCreatingHeaders()
    {
        $_SERVER['FOO_BAR'] = 'baz';

        $result = HttpHeaders::fromGlobalRequestHeaders();

        unset($_SERVER['FOO_BAR']);

        $this->assertSame([], $result->getAll());
    }
    
    public function testHeadersCreatedFromGlobalsAreNormalized()
    {
        $_SERVER['HTTP_FOO'] = 'bar';

        $result = HttpHeaders::fromGlobalRequestHeaders();

        unset($_SERVER['HTTP_FOO']);

        $this->assertTrue($result->has('foo'));

    }
}
