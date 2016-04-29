<?php

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Context\Website\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Context\Website\Exception\UnknownWebsiteUrlException;

/**
 * @covers \LizardsAndPumpkins\Context\Website\ConfigurableUrlToWebsiteMap
 * @uses   \LizardsAndPumpkins\Context\Website\Website
 */
class ConfigurableUrlToWebsiteMapTest extends \PHPUnit_Framework_TestCase
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
        $this->stubConfigReader = $this->getMock(ConfigReader::class);
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
        $this->expectExceptionMessage(sprintf('No website code found for url "%s"', $url));

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
     * @param $testMap
     * @param $testUrl
     * @param $expectedWebsiteCode
     */
    public function testFirstMatchingWebsiteCodeIsReturned($testMap, $testUrl, $expectedWebsiteCode)
    {
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($testMap);
        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $result = $websiteMap->getWebsiteCodeByUrl($testUrl);

        $this->assertWebsiteEqual(Website::fromString($expectedWebsiteCode), $result);
    }

    /**
     * @return array[]
     */
    public function websiteMapProvider()
    {
        return [
            ['^http://example\.com/=foo,^https://127\.0\.0\.1=bar', 'http://example.com/', 'foo'],
            ['^http://example\.com/=foo,^https://127\.0\.0\.1=bar', 'https://127.0.0.1', 'bar'],
            ['^http://example\.com/=foo,^http://example\.com/=bar', 'http://example.com/', 'bar'],
            ['^https?://example\.com/=foo', 'http://example.com/', 'foo'],
            ['^https?://example\.com/=foo', 'https://example.com/', 'foo'],
        ];
    }
}
