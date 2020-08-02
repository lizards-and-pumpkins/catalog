<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\Website\Exception\NoConfiguredBaseUrlException;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Context\Context;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 */
class WebsiteBaseUrlBuilderTest extends TestCase
{
    private $testBaseUrl = 'http://example.com/';
    
    /**
     * @var WebsiteBaseUrlBuilder
     */
    private $websiteBaseUrlBuilder;

    /**
     * @var Context|MockObject
     */
    private $stubContext;

    /**
     * @var ConfigReader|MockObject
     */
    private $stubConfigReader;

    /**
     * @return ConfigReader|MockObject
     */
    private function createStubConfigReader() : ConfigReader
    {
        $stubConfigReader = $this->createMock(ConfigReader::class);
        $configKey = WebsiteBaseUrlBuilder::CONFIG_PREFIX . 'test_website';

        $stubConfigReader->method('has')->willReturnCallback(function (string $requestedConfigKey) use ($configKey) {
            return $configKey === $requestedConfigKey;
        });

        $stubConfigReader->method('get')->with($configKey)->willReturn($this->testBaseUrl);

        return $stubConfigReader;
    }

    /**
     * @return Context|MockObject
     */
    private function createStubContext() : Context
    {
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('getValue')->with(Website::CONTEXT_CODE)->willReturn('test_website');
        return $stubContext;
    }

    final protected function setUp(): void
    {
        $this->stubConfigReader = $this->createStubConfigReader();
        $this->websiteBaseUrlBuilder = new WebsiteBaseUrlBuilder($this->stubConfigReader);

        $this->stubContext = $this->createStubContext();
    }

    public function testItReturnsABaseUrlInstance(): void
    {
        $this->assertInstanceOf(BaseUrl::class, $this->websiteBaseUrlBuilder->create($this->stubContext));
    }

    public function testItCreatesTheBaseUrlBasedOnTheValueReturnedByTheConfigReader(): void
    {
        $this->assertSame($this->testBaseUrl, (string) $this->websiteBaseUrlBuilder->create($this->stubContext));
    }

    public function testItImplementsTheBaseUrlBuilderInterface(): void
    {
        $this->assertInstanceOf(BaseUrlBuilder::class, $this->websiteBaseUrlBuilder);
    }

    public function testItThrowsAnExceptionIfTheConfigReaderReturnsNoValue(): void
    {
        $this->expectException(NoConfiguredBaseUrlException::class);
        $this->expectExceptionMessage('No base URL configuration found for the website "test_website"');

        /** @var ConfigReader|MockObject $emptyStubConfigReader */
        $emptyStubConfigReader = $this->createMock(ConfigReader::class);
        $emptyStubConfigReader->method('has')->willReturn(false);

        (new WebsiteBaseUrlBuilder($emptyStubConfigReader))->create($this->stubContext);
    }
}
