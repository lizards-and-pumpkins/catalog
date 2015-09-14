<?php

namespace LizardsAndPumpkins\Http;

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
        $this->setExpectedException(UnknownProtocolException::class);
        HttpUrl::fromString('ftp://user:pass@example.com');
    }

    public function testProtocolIsNotEncrypted()
    {
        $this->assertFalse($this->url->isProtocolEncrypted());
    }

    public function testExceptionIsThrownIfNotValidUrlIsPassed()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        HttpUrl::fromString('this is not a valid url');
    }

    public function testDirectoryPathIsExcludedFromUrl()
    {
        $originalScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/path/to/index.php';

        $url = HttpUrl::fromString('http://www.example.com/path/to/some-page');
        $result = $url->getPathRelativeToWebFront();

        $_SERVER['SCRIPT_NAME'] = $originalScriptName;

        $this->assertEquals('some-page', $result);
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

    public function testAllQueryParametersAreReturnedExceptGiven()
    {
        $url = HttpUrl::fromString('http://example.com/?foo=bar&baz=qux');
        $result = $url->getQueryParametersExceptGiven('foo');

        $this->assertSame(['baz' => 'qux'], $result);
    }
}
