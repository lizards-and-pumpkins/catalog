<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\SelectProductListingRobotsMetaTagContent
 */
class SelectProductListingRobotsMetaTagContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var SelectProductListingRobotsMetaTagContent
     */
    private $selector;

    protected function setUp()
    {
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->selector = new SelectProductListingRobotsMetaTagContent();
    }

    public function testReturnsAllIfNoQueryParametersArePresent()
    {
        $this->stubRequest->method('hasQueryParameters')->willReturn(false);
        $this->assertSame('all', $this->selector->getRobotsMetaTagContentForRequest($this->stubRequest));
    }

    public function testReturnsNoindexIfQueryParametersArePresent()
    {
        $this->stubRequest->method('hasQueryParameters')->willReturn(true);
        $this->assertSame('noindex', $this->selector->getRobotsMetaTagContentForRequest($this->stubRequest));
    }
}
