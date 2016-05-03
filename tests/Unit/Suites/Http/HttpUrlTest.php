<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\UnknownProtocolException;

/**
 * @covers \LizardsAndPumpkins\Http\HttpUrl
 */
class HttpUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpUrl
     */
    private $url;

    /**
     * @var string
     */
    private $urlString = 'http://example.com/path';

    public function setUp()
    {
        $this->url = HttpUrl::fromString($this->urlString);
    }

    public function testPathIsReturned()
    {
        $this->assertEquals('/path', $this->url->getPath());
    }

    public function testSlashIsReturnedAsPathIfNoPathIsGiven()
    {
        $url = HttpUrl::fromString('http://example.com');
        $this->assertEquals('/', $url->getPath());
    }

    public function testSlashIsReturnedAsPathIfSlashPathIsGiven()
    {
        $url = HttpUrl::fromString('http://example.com/');
        $this->assertEquals('/', $url->getPath());
    }

    public function testUrlIsReturned()
    {
        $this->assertEquals($this->urlString, (string) $this->url);
    }

    public function testExceptionIsThrownForNonHttpRequest()
    {
        $this->expectException(UnknownProtocolException::class);
        HttpUrl::fromString('ftp://user:pass@example.com');
    }

    public function testProtocolIsNotEncrypted()
    {
        $this->assertFalse($this->url->isProtocolEncrypted());
    }

    public function testExceptionIsThrownIfNotValidUrlIsPassed()
    {
        $this->expectException(\InvalidArgumentException::class);
        HttpUrl::fromString('this is not a valid url');
    }

    public function testDirectoryPathIsExcludedFromUrl()
    {
        $originalScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/path/to/index.php';

        $url = HttpUrl::fromString('http://www.example.com/path/to/some-page');
        $result = $url->getPathWithoutWebsitePrefix();

        $_SERVER['SCRIPT_NAME'] = $originalScriptName;

        $this->assertEquals('some-page', $result);
    }

    public function testLastTokenOfDirectoryPathIsIncludedIntoPath()
    {
        $originalScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/path/to/index.php';

        $url = HttpUrl::fromString('http://www.example.com/path/to/some-page');
        $result = $url->getPathWithWebsitePrefix();

        $_SERVER['SCRIPT_NAME'] = $originalScriptName;

        $this->assertEquals('to/some-page', $result);
    }

    public function testEmptyStringIsReturnedIfParameterIsAbsentInRequestQuery()
    {
        $this->assertNull($this->url->getQueryParameter('foo'));
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
     * @param string $host
     * @param string $expected
     */
    public function testItReturnsTheRequestHost($host, $expected)
    {
        $url = HttpUrl::fromString('http://' . $host . '/path/to/some-page');
        $this->assertSame($expected, $url->getHost());
    }

    /**
     * @return array[]
     */
    public function requestHostDataProvider()
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
