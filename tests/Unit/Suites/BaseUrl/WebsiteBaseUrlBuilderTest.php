<?php


namespace LizardsAndPumpkins\Context\BaseUrl\BaseUrl;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrl;
use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder;
use LizardsAndPumpkins\Context\Website\Exception\NoConfiguredBaseUrlException;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Website\ContextWebsite;

/**
 * @covers \LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
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
     * @param string $baseUrlString
     * @return ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubConfigReader($baseUrlString)
    {
        $stubConfigReader = $this->getMock(ConfigReader::class);
        $configKey = WebsiteBaseUrlBuilder::CONFIG_PREFIX . 'test_website';
        $stubConfigReader->method('get')->with($configKey)->willReturn($baseUrlString);
        return $stubConfigReader;
    }

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContext()
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->with(ContextWebsite::CODE)->willReturn('test_website');
        return $stubContext;
    }

    protected function setUp()
    {
        $this->stubConfigReader = $this->createStubConfigReader($this->testBaseUrl);
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

    public function testItThrowsAnExceptionIfTheConfigReaderReturnsNoValue()
    {
        $this->expectException(NoConfiguredBaseUrlException::class);
        $this->expectExceptionMessage('No base URL configuration found for the website "test_website"');
        $emptyBaseUrl = null;
        $emptyStubConfigReader = $this->createStubConfigReader($emptyBaseUrl);
        (new WebsiteBaseUrlBuilder($emptyStubConfigReader))->create($this->stubContext);
    }
}
