<?php

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\Website\Exception\UnableToDetermineContextWebsiteException;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Context\Website\ContextWebsite
 */
class ContextWebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextWebsite
     */
    private $contextWebsite;

    /**
     * @var UrlToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubWebsiteMap;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    protected function setUp()
    {
        $this->stubWebsiteMap = $this->getMock(UrlToWebsiteMap::class);
        $this->contextWebsite = new ContextWebsite($this->stubWebsiteMap);
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextWebsite);
    }

    public function testItReturnsTheWebsiteCode()
    {
        $this->assertSame(ContextWebsite::CODE, $this->contextWebsite->getCode());
    }

    public function testItThrowsAnExceptionIfTheWebsiteCanNotBeDeterminedFromTheInputArray()
    {
        $this->expectException(UnableToDetermineContextWebsiteException::class);
        $this->expectExceptionMessage(
            'Unable to determine context website because neither the ' .
            'website nor the request are set in the input array.'
        );
        
        $inputDataSet = [];
        
        $this->contextWebsite->getValue($inputDataSet);
    }

    /**
     * @param string $websiteCode
     * @dataProvider websiteCodeProvider
     */
    public function testItReturnsTheWebsiteIfPresentInTheInput($websiteCode)
    {
        $inputDataSet = [ContextWebsite::CODE => $websiteCode];
        $this->assertSame($websiteCode, $this->contextWebsite->getValue($inputDataSet));
    }

    /**
     * @return array[]
     */
    public function websiteCodeProvider()
    {
        return [['webA'], ['webB']];
    }

    public function testItReturnsTheWebsiteBasedOnTheRequestIfNotExplicitlySet()
    {
        $this->stubWebsiteMap->method('getWebsiteCodeByUrl')->willReturn('web');
        $this->stubRequest->method('getUrl')->willReturn('example.com');

        $inputDataSet = [ContextBuilder::REQUEST => $this->stubRequest];
        
        $this->assertSame('web', $this->contextWebsite->getValue($inputDataSet));
    }
}
