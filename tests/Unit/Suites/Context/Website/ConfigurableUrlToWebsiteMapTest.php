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
     * @var ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubConfigReader;

    private function assertWebsiteEqual(Website $expected, Website $actual)
    {
        $message = sprintf('Expected website "%s", got "%s"', $expected, $actual);
        $this->assertTrue($actual->isEqual($expected), $message);
    }

    protected function setUp()
    {
        $this->stubConfigReader = $this->createMock(ConfigReader::class);
    }

    public function testWebsiteMapCanBeCreatedFromConfigValue()
    {
        $result = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertInstanceOf(ConfigurableUrlToWebsiteMap::class, $result);
    }

    public function testExceptionIsThrownIfGivenUrlMatchesNoneOfWebsites()
    {
        $url = 'http://www.example.com/';

        $this->expectException(UnknownWebsiteUrlException::class);
        $this->expectExceptionMessage(sprintf('No website found for url "%s"', $url));

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $websiteMap->getWebsiteCodeByUrl($url);
    }

    public function testExceptionIsThrownIfMapConfigurationFormatIsMalformed()
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
    public function testFirstMatchingWebsiteCodeIsReturned(string $testMap, string $testUrl, string $expectedCode)
    {
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($testMap);
        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $result = $websiteMap->getWebsiteCodeByUrl($testUrl);

        $this->assertWebsiteEqual(Website::fromString($expectedCode), $result);
    }

    /**
     * @return array[]
     */
    public function websiteMapProvider() : array
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

    public function testThrowsAnExceptionIfTheWebsiteCanNotBeDetermined()
    {
        $url = 'http://www.example.com/';

        $this->expectException(UnknownWebsiteUrlException::class);
        $this->expectExceptionMessage(sprintf('No website found for url "%s"', $url));

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $websiteMap->getRequestPathWithoutWebsitePrefix($url);
    }
    
    public function testReturnsTheRequestPathWithoutUrlPrefix()
    {
        $testMap = 'http://example.com/aa/=foo|http://example.com/=bar';
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($testMap);

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertSame('a/b/c?d=e', $websiteMap->getRequestPathWithoutWebsitePrefix('http://example.com/aa/a/b/c?d=e'));
        $this->assertSame('foo', $websiteMap->getRequestPathWithoutWebsitePrefix('http://example.com/foo'));
    }
}
