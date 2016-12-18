<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\InvalidUrlStringException;
use LizardsAndPumpkins\Http\Exception\UnknownProtocolException;

/**
 * @covers \LizardsAndPumpkins\Http\HttpUrl
 */
class HttpUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider urlStringProvider
     */
    public function testReturnsUrlString(string $urlString)
    {
        $this->assertSame($urlString, (string) HttpUrl::fromString($urlString));
    }

    /**
     * @return array[]
     */
    public function urlStringProvider() : array
    {
        return [
            ['http://example.com'],
            ['https://example.com'],
            ['//example.com'],
            ['http://example.com/'],
            ['http://example.com/path'],
            ['http://example.com/path/path/path/'],
            ['http://example.com?foo=bar'],
            ['http://example.com/path/?foo=bar'],
        ];
    }

    public function testThrowsAnExceptionForNonHttpUrls()
    {
        $this->expectException(UnknownProtocolException::class);
        HttpUrl::fromString('ftp://user:pass@example.com');
    }

    public function testThrowsAnExceptionDuringAttemptToCreateUrlFromInvalidString()
    {
        $this->expectException(InvalidUrlStringException::class);
        HttpUrl::fromString('this is not a valid url');
    }

    public function testReturnsPathWithoutWebsitePrefix()
    {
        $originalScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/path/to/index.php';

        $url = HttpUrl::fromString('http://www.example.com/path/to/some-page');
        $result = $url->getPathWithoutWebsitePrefix();

        $_SERVER['SCRIPT_NAME'] = $originalScriptName;

        $this->assertEquals('some-page', $result);
    }

    public function testNullIsReturnedIfParameterIsAbsentInRequestQuery()
    {
        $url = HttpUrl::fromString('http://example.com/path');
        $this->assertNull($url->getQueryParameter('foo'));
    }

    public function testQueryParameterIsReturned()
    {
        $url = HttpUrl::fromString('http://example.com/?foo=bar&baz=qux');
        $result = $url->getQueryParameter('foo');

        $this->assertEquals('bar', $result);
    }

    public function testReturnsTrueIfThereAreQueryParameters()
    {
        $url = HttpUrl::fromString('http://example.com/?foo=bar&baz=qux');
        $this->assertTrue($url->hasQueryParameters());
    }

    public function testReturnsFalseIfThereAreQueryParameters()
    {
        $url = HttpUrl::fromString('http://example.com/foo/');
        $this->assertFalse($url->hasQueryParameters());
    }

    /**
     * @dataProvider requestHostDataProvider
     */
    public function testReturnsHost(string $host, string $expected)
    {
        $url = HttpUrl::fromString('http://' . $host . '/path/to/some-page');
        $this->assertSame($expected, $url->getHost());
    }

    /**
     * @return array[]
     */
    public function requestHostDataProvider() : array
    {
        return [
            'top'      => ['example.com', 'example.com'],
            'sub'      => ['www.example.com', 'www.example.com'],
            'special'  => ['über.com', 'über.com'],
            'punycode' => ['xn--ber-goa.com', 'über.com'],
            'ip4'      => ['127.0.0.1', '127.0.0.1']
        ];
    }
}
