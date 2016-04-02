<?php

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Context\Website\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Context\Website\Exception\UnknownWebsiteHostException;

/**
 * @covers \LizardsAndPumpkins\Context\Website\ConfigurableHostToWebsiteMap
 * @uses   \LizardsAndPumpkins\Context\Website\Website
 */
class ConfigurableHostToWebsiteMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableHostToWebsiteMap
     */
    private $websiteMap;

    /**
     * @var string[]
     */
    private $testMap = [
        'example.com' => 'web1',
        '127.0.0.1'   => 'exampleDev',
    ];

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
        $this->websiteMap = ConfigurableHostToWebsiteMap::fromArray($this->testMap);
        $this->stubConfigReader = $this->getMock(ConfigReader::class);
    }

    public function testItThrowsAnExceptionIfAHostNameIsNotKnown()
    {
        $this->expectException(UnknownWebsiteHostException::class);
        $this->expectExceptionMessage('No website code found for host "www.example.com"');
        $this->websiteMap->getWebsiteCodeByHost('www.example.com');
    }

    public function testItReturnsTheCodeIfSet()
    {
        $websiteOne = Website::fromString($this->testMap['example.com']);
        $websiteTwo = Website::fromString($this->testMap['127.0.0.1']);
        $this->assertWebsiteEqual($websiteOne, $this->websiteMap->getWebsiteCodeByHost('example.com'));
        $this->assertWebsiteEqual($websiteTwo, $this->websiteMap->getWebsiteCodeByHost('127.0.0.1'));
    }

    public function testItReturnsAWebsiteMapInstance()
    {
        $instance = ConfigurableHostToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertInstanceOf(ConfigurableHostToWebsiteMap::class, $instance);
    }

    public function testItUsesAMapFromTheConfiguration()
    {
        $map = 'example.com=aaa|127.0.0.1=bbb';
        $this->stubConfigReader->method('get')->with(ConfigurableHostToWebsiteMap::CONFIG_KEY)->willReturn($map);

        $websiteMap = ConfigurableHostToWebsiteMap::fromConfig($this->stubConfigReader);

        $this->assertWebsiteEqual(Website::fromString('aaa'), $websiteMap->getWebsiteCodeByHost('example.com'));
        $this->assertWebsiteEqual(Website::fromString('bbb'), $websiteMap->getWebsiteCodeByHost('127.0.0.1'));
    }

    public function testItThrowsAnExceptionIfAMapValueNotMatchesTheExpectedFormat()
    {
        $this->expectException(InvalidWebsiteMapConfigRecordException::class);
        $this->expectExceptionMessage('Unable to parse the website to code mapping record "test="');
        $map = 'test=';
        $this->stubConfigReader->method('get')->willReturn($map);

        ConfigurableHostToWebsiteMap::fromConfig($this->stubConfigReader);
    }
}
