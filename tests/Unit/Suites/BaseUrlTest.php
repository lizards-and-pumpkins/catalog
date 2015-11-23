<?php


namespace LizardsAndPumpkins;

class BaseUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testItShouldThrowAnExceptionIfTheSourceIsNotAString()
    {
        $this->setExpectedException(
            Exception\InvalidBaseUrlSourceDataException::class,
            'The input for the base URL has to be a string, got '
        );
        BaseUrl::fromString(123);
    }

    public function testItThrowsAnExceptionIfTheSourceStringIsEmpty()
    {
        $this->setExpectedException(
            Exception\InvalidBaseUrlSourceDataException::class,
            'Invalid empty source data for the base URL specified'
        );
        BaseUrl::fromString(' ');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotContainTheProtocol()
    {
        $this->setExpectedException(
            Exception\InvalidBaseUrlSourceDataException::class,
            'The base URL input string does not contain the protocol'
        );
        BaseUrl::fromString('example.com/');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotEndWithASlash()
    {
        $this->setExpectedException(
            Exception\InvalidBaseUrlSourceDataException::class,
            'The base URL input string does not end with the required trailing slash'
        );
        BaseUrl::fromString('http://example.com');
    }

    public function testItThrowsAnExceptionIfTheInputStringDoesNotMatchAValdDomainAndRequestPath()
    {
        $invalidBaseUrl = 'http://example_domain.com/';
        $this->setExpectedException(
            Exception\InvalidBaseUrlSourceDataException::class,
            sprintf('The base URL "%s" is invalid', $invalidBaseUrl)
        );
        BaseUrl::fromString($invalidBaseUrl);
    }

    public function testItReturnsABaseUrlInstance()
    {
        $this->assertInstanceOf(BaseUrl::class, BaseUrl::fromString('https://example.com/'));
    }

    public function testItCanBeCastToAString()
    {
        $baseUrlString = 'http://example.com/';
        $this->assertSame($baseUrlString, (string) BaseUrl::fromString($baseUrlString));
    }
}
