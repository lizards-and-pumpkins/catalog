<?php


namespace LizardsAndPumpkins\BaseUrl;

use LizardsAndPumpkins\BaseUrl;
use LizardsAndPumpkins\ConfigReader;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\WebsiteContextDecorator;

/**
 * @covers \LizardsAndPumpkins\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\BaseUrl\HttpBaseUrl
 */
class WebsiteBaseUrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $testBaseUrl = 'http://example.com/';
    
    /**
     * @var WebsiteBaseUrlBuilder
     */
    private $websiteBaseUrlBuilder;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubConfigReader;

    /**
     * @return ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubConfigReader()
    {
        $stubConfigReader = $this->getMock(ConfigReader::class);
        $configKey = WebsiteBaseUrlBuilder::CONFIG_PREFIX . 'test_website';
        $stubConfigReader->method('get')->with($configKey)->willReturn($this->testBaseUrl);
        return $stubConfigReader;
    }

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContext()
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->with(WebsiteContextDecorator::CODE)->willReturn('test_website');
        return $stubContext;
    }

    protected function setUp()
    {
        $this->stubConfigReader = $this->createStubConfigReader();
        $this->websiteBaseUrlBuilder = new WebsiteBaseUrlBuilder($this->stubConfigReader);

        $this->stubContext = $this->createStubContext();
    }

    public function testItReturnsABaseUrlInstance()
    {
        $this->assertInstanceOf(BaseUrl::class, $this->websiteBaseUrlBuilder->create($this->stubContext));
    }

    public function testItCreatesTheBaseUrlBasedOnTheValueReturnedByTheConfigReader()
    {
        $this->assertSame($this->testBaseUrl, (string) $this->websiteBaseUrlBuilder->create($this->stubContext));
    }

    public function testItImplementsTheBaseUrlBuilderInterface()
    {
        $this->assertInstanceOf(BaseUrlBuilder::class, $this->websiteBaseUrlBuilder);
    }
}
