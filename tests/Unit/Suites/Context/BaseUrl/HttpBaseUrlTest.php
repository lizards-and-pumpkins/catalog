<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\BaseUrl\Exception\InvalidBaseUrlSourceDataException;

/**
 * @covers \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 */
class HttpBaseUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testItShouldThrowAnExceptionIfTheSourceIsNotAString()
    {
        $this->expectException(\TypeError::class);
        new HttpBaseUrl(123);
    }

    public function testItThrowsAnExceptionIfTheSourceStringIsEmpty()
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('Invalid empty source data for the base URL specified');
        new HttpBaseUrl(' ');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotContainTheProtocol()
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('The base URL input string contains an invalid protocol');
        new HttpBaseUrl('example.com/');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotEndWithASlash()
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('The base URL input string does not end with the required trailing slash');
        new HttpBaseUrl('http://example.com');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotContainAValidDomain()
    {
        $baseUrlWithInvalidDomain = 'http://example_domain.com/';
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage(sprintf('The base URL "%s" is invalid', $baseUrlWithInvalidDomain));
        new HttpBaseUrl($baseUrlWithInvalidDomain);
    }

    public function testItCanBeCastToAString()
    {
        $baseUrlString = 'http://example.com/';
        $this->assertSame($baseUrlString, (string) new HttpBaseUrl($baseUrlString));
    }

    public function testItImplementsTheBaseUrlInterface()
    {
        $this->assertInstanceOf(BaseUrl::class, new HttpBaseUrl('http://example.com/foo/'));
    }
}
