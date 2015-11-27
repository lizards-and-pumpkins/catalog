<?php


namespace LizardsAndPumpkins\Website;

use LizardsAndPumpkins\ConfigReader;
use LizardsAndPumpkins\Website\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Website\Exception\UnknownWebsiteHostException;

/**
 * @covers \LizardsAndPumpkins\ConfigurableHostToWebsiteMap
 */
class ConfigurableHostToWebsiteMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableHostToWebsiteMap
     */
    private $websiteMap;
    
    private $testMap = [
        'example.com' => 'web1',
        '127.0.0.1' => 'dev',
    ];

    /**
     * @var ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubConfigReader;

    protected function setUp()
    {
        $this->websiteMap = ConfigurableHostToWebsiteMap::fromArray($this->testMap);
        $this->stubConfigReader = $this->getMock(ConfigReader::class);
    }
    
    public function testItThrowsAnExceptionIfAHostNameIsNotKnown()
    {
        $this->setExpectedException(
            UnknownWebsiteHostException::class,
            'No website code found for host "www.example.com"'
        );
        $this->websiteMap->getWebsiteCodeByHost('www.example.com');
    }

    public function testItReturnsTheCodeIfSet()
    {
        $this->assertSame($this->testMap['example.com'], $this->websiteMap->getWebsiteCodeByHost('example.com'));
        $this->assertSame($this->testMap['127.0.0.1'], $this->websiteMap->getWebsiteCodeByHost('127.0.0.1'));
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
        
        $this->assertSame('aaa', $websiteMap->getWebsiteCodeByHost('example.com'));
        $this->assertSame('bbb', $websiteMap->getWebsiteCodeByHost('127.0.0.1'));
    }

    public function testItThrowsAnExceptionIfAMapValueNotMatchesTheExpectedFormat()
    {
        $this->setExpectedException(
            InvalidWebsiteMapConfigRecordException::class,
            'Unable to parse the website to code mapping record "test="'
        );
        $map = 'test=';
        $this->stubConfigReader->method('get')->willReturn($map);

        ConfigurableHostToWebsiteMap::fromConfig($this->stubConfigReader);
    }
}
