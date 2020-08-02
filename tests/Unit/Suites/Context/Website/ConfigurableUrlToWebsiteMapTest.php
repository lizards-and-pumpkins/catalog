<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Context\Website\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Context\Website\Exception\UnknownWebsiteUrlException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\Website\ConfigurableUrlToWebsiteMap
 * @uses   \LizardsAndPumpkins\Context\Website\Website
 */
class ConfigurableUrlToWebsiteMapTest extends TestCase
{
    /**
     * @var ConfigReader|MockObject
     */
    private $stubConfigReader;

    private function assertWebsiteEqual(Website $expected, Website $actual): void
    {
        $message = sprintf('Expected website "%s", got "%s"', $expected, $actual);
        $this->assertTrue($actual->isEqual($expected), $message);
    }

    final protected function setUp(): void
    {
        $this->stubConfigReader = $this->createMock(ConfigReader::class);
    }

    public function testWebsiteMapCanBeCreatedFromConfigValue(): void
    {
        $result = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertInstanceOf(ConfigurableUrlToWebsiteMap::class, $result);
    }

    public function testThrowsExceptionIfGivenUrlMatchesNoneOfWebsites(): void
    {
        $url = 'http://www.example.com/';

        $this->expectException(UnknownWebsiteUrlException::class);
        $this->expectExceptionMessage(sprintf('No website found for url "%s"', $url));

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $websiteMap->getWebsiteCodeByUrl($url);
    }

    public function testExceptionIsThrownIfMapConfigurationFormatIsMalformed(): void
    {
        $this->expectException(InvalidWebsiteMapConfigRecordException::class);
        $this->expectExceptionMessage('Unable to parse the website to code mapping record "test="');

        $map = 'test=';
        $this->stubConfigReader->method('get')->willReturn($map);

        ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
    }

    /**
     * @dataProvider websiteMapProvider
     */
    public function testFirstMatchingWebsiteCodeIsReturned(string $testMap, string $testUrl, string $expectedCode): void
    {
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($testMap);
        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $result = $websiteMap->getWebsiteCodeByUrl($testUrl);

        $this->assertWebsiteEqual(Website::fromString($expectedCode), $result);
    }

    /**
     * @return array[]
     */
    public function websiteMapProvider(): array
    {
        return [
            ['http://example.com/=foo|https://127.0.0.1=bar', 'http://example.com/', 'foo'],
            ['http://example.com/=foo|https://127.0.0.1=bar', 'https://127.0.0.1', 'bar'],
            ['http://example.com/=foo|http://example.com/=bar', 'http://example.com/', 'bar'],
            ['http://example.com/=foo|https://example.com/=bar', 'http://example.com/', 'foo'],
            ['http://example.com/foo/=foo|http://example.com/bar/=bar', 'http://example.com/bar/baz', 'bar'],
            ['http://example.com/aa/=foo|http://example.com/=bar', 'http://example.com/aa/baz', 'foo'],
            ['http://example.com/aa/=foo|http://example.com/=bar', 'http://example.com/baz', 'bar'],
            ['http://example.com/=bar|http://example.com/aa/=foo', 'http://example.com/aa/baz', 'bar'],
        ];
    }

    public function testReturnsTheRequestPathWithoutUrlPrefix(): void
    {
        $testMap = 'http://example.com/aa/=foo|http://example.com/=bar';
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($testMap);

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertSame('a/b/c', $websiteMap->getRequestPathWithoutWebsitePrefix('http://example.com/aa/a/b/c'));
        $this->assertSame('foo', $websiteMap->getRequestPathWithoutWebsitePrefix('http://example.com/foo'));
    }

    public function testReturnsTheRequestPathWithoutUrlPrefixWithoutQueryArguments(): void
    {
        $testMap = 'http://example.com/aa/=foo|http://example.com/=bar';
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($testMap);

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertSame('a/b/c', $websiteMap->getRequestPathWithoutWebsitePrefix('http://example.com/aa/a/b/c?a=b'));
    }

    public function testReturnsTheRequestPathWithoutUrlPrefixWithoutLeadingSlash(): void
    {
        $testMap = 'http://example.com/aa=foo|http://example.com=bar';
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($testMap);

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertSame('a/b/c', $websiteMap->getRequestPathWithoutWebsitePrefix('http://example.com/aa/a/b/c'));
    }

    public function testReturnsTheRequestPathWithoutUrlPrefixWithoutTrailingSlash(): void
    {
        $testMap = 'http://example.com/aa/=foo|http://example.com/=bar';
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($testMap);

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertSame('a/b/c', $websiteMap->getRequestPathWithoutWebsitePrefix('http://example.com/aa/a/b/c/?a=b'));
    }
}
