<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\BaseUrl\Exception\InvalidBaseUrlSourceDataException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 */
class HttpBaseUrlTest extends TestCase
{
    public function testThrowsAnExceptionIfTheSourceIsNotAString(): void
    {
        $this->expectException(\TypeError::class);
        new HttpBaseUrl(123);
    }

    public function testThrowsAnExceptionIfTheSourceStringIsEmpty(): void
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('Invalid empty source data for the base URL specified');
        new HttpBaseUrl(' ');
    }

    public function testThrowsAnExceptionIfTheInputStringDoesNotContainTheProtocol(): void
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('The base URL input string contains an invalid protocol');
        new HttpBaseUrl('example.com/');
    }

    public function testThrowsAnExceptionIfTheInputStringDoesNotEndWithASlash(): void
    {
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage('The base URL input string does not end with the required trailing slash');
        new HttpBaseUrl('http://example.com');
    }

    public function testThrowsAnExceptionIfTheInputStringDoesNotContainAValidDomain(): void
    {
        $baseUrlWithInvalidDomain = 'http://example_domain.com/';
        $this->expectException(InvalidBaseUrlSourceDataException::class);
        $this->expectExceptionMessage(sprintf('The base URL "%s" is invalid', $baseUrlWithInvalidDomain));
        new HttpBaseUrl($baseUrlWithInvalidDomain);
    }

    public function testCanBeCastToAString(): void
    {
        $baseUrlString = 'http://example.com/';
        $this->assertSame($baseUrlString, (string) new HttpBaseUrl($baseUrlString));
    }

    public function testCanContainPort(): void
    {
        $this->assertInstanceOf(HttpBaseUrl::class, new HttpBaseUrl('http://example.com:80/'));
    }

    public function testImplementsTheBaseUrlInterface(): void
    {
        $this->assertInstanceOf(BaseUrl::class, new HttpBaseUrl('http://example.com/foo/'));
    }
}
