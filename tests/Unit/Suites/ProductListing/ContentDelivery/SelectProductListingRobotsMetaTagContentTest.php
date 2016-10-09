<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\SelectProductListingRobotsMetaTagContent
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
        $this->stubRequest = $this->createMock(HttpRequest::class);
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
