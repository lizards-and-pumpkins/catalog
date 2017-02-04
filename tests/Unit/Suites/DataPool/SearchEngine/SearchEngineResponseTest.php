<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\ProductId;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse
 */
class SearchEngineResponseTest extends TestCase
{
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var FacetFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldCollection;

    /**
     * @var SearchEngineResponse
     */
    private $searchEngineResponse;

    private $testTotalNumberOfResults = 5;

    protected function setUp()
    {
        $this->stubProductId = $this->createMock(ProductId::class);
        $this->stubFacetFieldCollection = $this->createMock(FacetFieldCollection::class);

        $this->searchEngineResponse = new SearchEngineResponse(
            $this->stubFacetFieldCollection,
            $this->testTotalNumberOfResults,
            $this->stubProductId
        );
    }

    public function testProductIdsAreReturned()
    {
        $this->assertSame([$this->stubProductId], $this->searchEngineResponse->getProductIds());
    }

    public function testSearchEngineFacetFieldCollectionIsReturned()
    {
        $this->assertSame($this->stubFacetFieldCollection, $this->searchEngineResponse->getFacetFieldCollection());
    }

    public function testTotalNumberOfResultsIsReturned()
    {
        $this->assertSame($this->testTotalNumberOfResults, $this->searchEngineResponse->getTotalNumberOfResults());
    }
}
