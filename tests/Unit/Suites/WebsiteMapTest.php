<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Exception\UnknownWebsiteHostException;

/**
 * @covers \LizardsAndPumpkins\WebsiteMap
 */
class WebsiteMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebsiteMap
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
        $this->websiteMap = WebsiteMap::fromArray($this->testMap);
        $this->stubConfigReader = $this->getMock(ConfigReader::class);
    }
    
    public function testItThrowsAnExceptionIfAHostNameIsNotKnown()
    {
        $this->setExpectedException(
            UnknownWebsiteHostException::class,
            'No website code found for host "www.example.com"'
        );
        $this->websiteMap->getCodeByHost('www.example.com');
    }

    public function testItReturnsTheCodeIfSet()
    {
        $this->assertSame($this->testMap['example.com'], $this->websiteMap->getCodeByHost('example.com'));
        $this->assertSame($this->testMap['127.0.0.1'], $this->websiteMap->getCodeByHost('127.0.0.1'));
    }

    public function testItReturnsAWebsiteMapInstance()
    {
        $this->assertInstanceOf(WebsiteMap::class, WebsiteMap::fromConfig($this->stubConfigReader));
    }

    public function testItUsesAMapFromTheConfiguration()
    {
        $map = 'example.com=aaa|127.0.0.1=bbb';
        $this->stubConfigReader->method('get')->with(WebsiteMap::CONFIG_KEY)->willReturn($map);

        $websiteMap = WebsiteMap::fromConfig($this->stubConfigReader);
        
        $this->assertSame('aaa', $websiteMap->getCodeByHost('example.com'));
        $this->assertSame('bbb', $websiteMap->getCodeByHost('127.0.0.1'));
    }

    public function testItThrowsAnExceptionIfAMapValueNotMatchesTheExpectedFormat()
    {
        $this->setExpectedException(
            InvalidWebsiteMapConfigRecordException::class,
            'Unable to parse the website to code mapping record "test="'
        );
        $map = 'test=';
        $this->stubConfigReader->method('get')->willReturn($map);

        WebsiteMap::fromConfig($this->stubConfigReader);
    }
}
