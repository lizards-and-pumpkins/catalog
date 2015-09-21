<?php


namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Exception\UnableToDetermineLocaleException;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

/**
 * @covers \LizardsAndPumpkins\Context\LocaleContextDecorator
 * @covers \LizardsAndPumpkins\Context\ContextDecorator
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 */
class LocaleContextDecoratorTest extends AbstractContextDecoratorTest
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return LocaleContextDecorator::CODE;
    }

    /**
     * @return mixed[]
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-locale'];
    }

    /**
     * @param Context $stubContext
     * @param mixed[] $stubContextData
     * @return LocaleContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new LocaleContextDecorator($stubContext, $stubContextData);
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

    public function testExceptionIsThrownIfNeitherLocaleNorRequestArePresent()
    {
        $this->setExpectedException(
            UnableToDetermineLocaleException::class,
            'Unable to determine locale from context source data ("locale" and "request" not present)'
        );
        $decorator = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), []);
        $decorator->getValue($this->getDecoratorUnderTestCode());
    }

    /**
     * @param mixed[] $sourceData
     * @param string $expected
     * @dataProvider localeSourceDataProvider
     */
    public function testItReturnsTheExpectedLocale(array $sourceData, $expected)
    {
        $localeContext = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), $sourceData);
        $this->assertSame($expected, $localeContext->getValue(LocaleContextDecorator::CODE));
    }

    /**
     * @return array[]
     */
    public function localeSourceDataProvider()
    {
        return [
            'locale' => [['locale' => 'xxx'], 'xxx'],
            'request de' => [['request' => $this->createTestRequest('http://example.com/xx_de')], 'de_DE'],
            'request en' => [['request' => $this->createTestRequest('http://example.com/xx_en')], 'en_US'],
            'default' => [['request' => $this->createTestRequest('http://example.com/')], 'de_DE'],
            'missing lang' => [['request' => $this->createTestRequest('http://example.com/xx')], 'de_DE'],
            'invalid lang' => [['request' => $this->createTestRequest('http://example.com/xx_xx')], 'de_DE'],
            'locale and request' => [
                ['request' => $this->createTestRequest('http://example.com/xx_en'), 'locale' => 'xxx'],
                'xxx'
            ],
        ];
    }
}
