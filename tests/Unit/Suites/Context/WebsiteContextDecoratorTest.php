<?php


namespace Brera\Context;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\Context\WebsiteContextDecorator
 * @covers \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Http\HttpRequest
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Http\HttpRequestBody
 */
class WebsiteContextDecoratorTest extends ContextDecoratorTestAbstract
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
            'request cy' => [['request' => $this->createTestRequest('http://example.com/cy')], 'cy'],
            'request ru_de' => [['request' => $this->createTestRequest('http://example.com/ru_de')], 'ru'],
            'request cy_de' => [['request' => $this->createTestRequest('http://example.com/cy_de')], 'cy'],
            'default' => [['request' => $this->createTestRequest('http://example.com/')], 'ru'],
            'website has priority' => [
                ['request' => $this->createTestRequest('http://example.com/ru_de'), 'website' => 'bbb'],
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
