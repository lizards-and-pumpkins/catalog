<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\Exception\UnableToDetermineContextWebsiteException;
use LizardsAndPumpkins\HostToWebsiteMap;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite
 */
class ContextWebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextWebsite
     */
    private $contextWebsite;

    /**
     * @var HostToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubWebsiteMap;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    protected function setUp()
    {
        $this->stubWebsiteMap = $this->getMock(HostToWebsiteMap::class);
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
        $this->setExpectedException(
            UnableToDetermineContextWebsiteException::class,
            'Unable to determine context website because neither the ' .
            'website nor the request are set in the input array.'
        );
        $inputDataSet = [];
        $otherContextParts = [];
        $this->contextWebsite->getValue($inputDataSet, $otherContextParts);
    }

    /**
     * @param string $websiteCode
     * @dataProvider websiteCodeProvider
     */
    public function testItReturnsTheWebsiteIfPresentInTheInput($websiteCode)
    {
        $inputDataSet = [ContextWebsite::CODE => $websiteCode];
        $otherContextParts = [];
        $this->assertSame($websiteCode, $this->contextWebsite->getValue($inputDataSet, $otherContextParts));
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
        $this->stubWebsiteMap->method('getWebsiteCodeByHost')->willReturn('web');
        $this->stubRequest->method('getHost')->willReturn('example.com');

        $inputDataSet = [ContextBuilder::REQUEST => $this->stubRequest];
        $otherContextParts = [];
        $this->assertSame('web', $this->contextWebsite->getValue($inputDataSet, $otherContextParts));
    }
}
