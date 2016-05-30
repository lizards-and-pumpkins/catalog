<?php

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\BaseUrl\Exception\InvalidBaseUrlSourceDataException;

/**
 * @covers \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 */
class HttpBaseUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testItShouldThrowAnExceptionIfTheSourceIsNotAString()
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('The input for the base URL has to be a string, got ');
        HttpBaseUrl::fromString(123);
    }

    public function testItThrowsAnExceptionIfTheSourceStringIsEmpty()
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('Invalid empty source data for the base URL specified');
        HttpBaseUrl::fromString(' ');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotContainTheProtocol()
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('The base URL input string contains an invalid protocol');
        HttpBaseUrl::fromString('example.com/');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotEndWithASlash()
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('The base URL input string does not end with the required trailing slash');
        HttpBaseUrl::fromString('http://example.com');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotContainAValidDomain()
    {
        $baseUrlWithInvalidDomain = 'http://example_domain.com/';
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage(sprintf('The base URL "%s" is invalid', $baseUrlWithInvalidDomain));
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
