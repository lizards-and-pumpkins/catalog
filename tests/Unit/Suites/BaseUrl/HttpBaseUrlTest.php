<?php


namespace LizardsAndPumpkins\BaseUrl;

use LizardsAndPumpkins\BaseUrl;
use LizardsAndPumpkins\BaseUrl\Exception\InvalidBaseUrlSourceDataException;

/**
 * @covers \LizardsAndPumpkins\BaseUrl\HttpBaseUrl
 */
class HttpBaseUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testItShouldThrowAnExceptionIfTheSourceIsNotAString()
    {
        $this->setExpectedException(
            InvalidBaseUrlSourceDataException::class,
            'The input for the base URL has to be a string, got '
        );
        HttpBaseUrl::fromString(123);
    }

    public function testItThrowsAnExceptionIfTheSourceStringIsEmpty()
    {
        $this->setExpectedException(
            InvalidBaseUrlSourceDataException::class,
            'Invalid empty source data for the base URL specified'
        );
        HttpBaseUrl::fromString(' ');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotContainTheProtocol()
    {
        $this->setExpectedException(
            InvalidBaseUrlSourceDataException::class,
            'The base URL input string does not contain the protocol'
        );
        HttpBaseUrl::fromString('example.com/');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotEndWithASlash()
    {
        $this->setExpectedException(
            InvalidBaseUrlSourceDataException::class,
            'The base URL input string does not end with the required trailing slash'
        );
        HttpBaseUrl::fromString('http://example.com');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotContainAValidDomain()
    {
        $baseUrlWithInvalidDomain = 'http://example_domain.com/';
        $this->setExpectedException(
            InvalidBaseUrlSourceDataException::class,
            sprintf('The base URL "%s" is invalid', $baseUrlWithInvalidDomain)
        );
        HttpBaseUrl::fromString($baseUrlWithInvalidDomain);
    }

    public function testItReturnsABaseUrlInstance()
    {
        $this->assertInstanceOf(HttpBaseUrl::class, HttpBaseUrl::fromString('https://example.com/'));
    }

    public function testItCanBeCastToAString()
    {
        $baseUrlString = 'http://example.com/';
        $this->assertSame($baseUrlString, (string) HttpBaseUrl::fromString($baseUrlString));
    }

    public function testItImplementsTheBaseUrlInterface()
    {
        $this->assertInstanceOf(BaseUrl::class, HttpBaseUrl::fromString('http://example.com/foo/'));
    }
}
