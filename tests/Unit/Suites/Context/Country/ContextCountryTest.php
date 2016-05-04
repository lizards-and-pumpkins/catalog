<?php

namespace LizardsAndPumpkins\Context\Country;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Context\Website\WebsiteToCountryMap;

/**
 * @covers \LizardsAndPumpkins\Context\Country\ContextCountry
 */
class ContextCountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextCountry
     */
    private $contextCountry;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var WebsiteToCountryMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubWebsiteToCountryMap;

    /**
     * @param string $cookieCountry
     */
    private function setRequestCountry($cookieCountry)
    {
        $json = json_encode(['country' => $cookieCountry]);
        $this->stubRequest->method('getCookieValue')->with(ContextCountry::COOKIE_NAME)->willReturn($json);
        $this->stubRequest->method('hasCookie')->with(ContextCountry::COOKIE_NAME)->willReturn(true);
    }

    protected function setUp()
    {
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->stubWebsiteToCountryMap = $this->getMock(WebsiteToCountryMap::class);
        $this->contextCountry = new ContextCountry($this->stubWebsiteToCountryMap);
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextCountry);
    }

    public function testItReturnsTheCountryContextPartCode()
    {
        $this->assertSame(ContextCountry::CODE, $this->contextCountry->getCode());
    }

    public function testItReturnsNullIfTheCountryIsNotInTheInputAndNoRequestIsPresentEither()
    {
        $inputDataSet = [];
        $this->assertNull($this->contextCountry->getValue($inputDataSet));
    }

    public function testItReturnsTheValueFromTheInputDataSetIfPresent()
    {
        $inputDataSet = [ContextCountry::CODE => 'fr'];
        
        $this->assertSame('fr', $this->contextCountry->getValue($inputDataSet));
    }

    public function testItReturnsTheCountryFromTheRequestIfNotPartOfTheInputDataSet()
    {
        $this->setRequestCountry('en');
        $inputDataSet = [ContextBuilder::REQUEST => $this->stubRequest];
        
        $this->assertSame('en', $this->contextCountry->getValue($inputDataSet));
    }

    public function testItReturnsTheDefaultCountryIfTheRequestDoesNotContainTheCountry()
    {
        $inputDataSet = [ContextBuilder::REQUEST => $this->stubRequest];
        $defaultCountryCode = 'default';
        
        $this->stubWebsiteToCountryMap->method('getDefaultCountry')->willReturn($defaultCountryCode);
        
        $this->assertSame($defaultCountryCode, $this->contextCountry->getValue($inputDataSet));
    }

    public function testItPrefersTheExplicitValueIfBothSourcesArePresentInTheInputDataSet()
    {
        $this->setRequestCountry('en');
        $inputDataSet = [
            ContextBuilder::REQUEST => $this->stubRequest,
            ContextCountry::CODE => 'fr'
        ];
        
        $this->assertSame('fr', $this->contextCountry->getValue($inputDataSet));
    }
}
