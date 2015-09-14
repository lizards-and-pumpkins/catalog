<?php


namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

/**
 * @covers \LizardsAndPumpkins\Context\WebsiteContextDecorator
 * @covers \LizardsAndPumpkins\Context\ContextDecorator
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 */
class WebsiteContextDecoratorTest extends AbstractContextDecoratorTest
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return WebsiteContextDecorator::CODE;
    }

    /**
     * @return mixed[]
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-website-code'];
    }
    
    /**
     * @param Context $stubContext
     * @param string[] $stubContextData
     * @return WebsiteContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new WebsiteContextDecorator($stubContext, $stubContextData);
    }

    /**
     * @param string $urlString
     * @return HttpRequest
     */
    private function createTestRequest($urlString)
    {
        return HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($urlString),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
    }

    public function testItThrowsExceptionIfNeitherWebsiteNorRequestArePresent()
    {
        $this->setExpectedException(
            UnableToDetermineWebsiteContextException::class,
            'Unable to determine website from context source data ("website" and "request" not present)'
        );
        $websiteContext = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), []);
        $websiteContext->getValue(WebsiteContextDecorator::CODE);
    }

    /**
     * @param mixed[] $sourceData
     * @param string $expected
     * @dataProvider websiteSourceDataProvider
     */
    public function testItReturnsTheExpectedWebsite(array $sourceData, $expected)
    {
        $websiteContext = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), $sourceData);
        $websiteValue = $websiteContext->getValue(WebsiteContextDecorator::CODE);
        $this->assertSame($expected, $websiteValue);
    }

    /**
     * @return array[]
     */
    public function websiteSourceDataProvider()
    {
        return [
            'website' => [['website' => 'test'], 'test'],
            'request ru' => [['request' => $this->createTestRequest('http://example.com/ru')], 'ru'],
            'request cy' => [['request' => $this->createTestRequest('http://example.com/cy')], 'ru'],
            'request _cy' => [['request' => $this->createTestRequest('http://example.com/_cy')], 'ru'],
            'request ru_de' => [['request' => $this->createTestRequest('http://example.com/ru_xx')], 'ru'],
            'request cy_de' => [['request' => $this->createTestRequest('http://example.com/cy_xx')], 'cy'],
            'default' => [['request' => $this->createTestRequest('http://example.com/')], 'ru'],
            'website has priority' => [
                ['request' => $this->createTestRequest('http://example.com/ru_xx'), 'website' => 'bbb'],
                'bbb'
            ],
        ];
    }

    public function testItReturnsTheDefaultWebsiteIfTheWebsiteRequestPathPartIsInvalid()
    {
        $sourceData = ['request' => $this->createTestRequest('http://example.com/invalid')];
        $websiteContext = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), $sourceData);
        $this->assertSame('ru', $websiteContext->getValue(WebsiteContextDecorator::CODE));
    }
}
