<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\Product\FilterNavigationFilterCollection;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse
 */
class SearchEngineResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentCollection;

    /**
     * @var FilterNavigationFilterCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFilterCollection;

    /**
     * @var SearchEngineResponse
     */
    private $searchEngineResponse;

    protected function setUp()
    {
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $this->stubFilterCollection = $this->getMock(FilterNavigationFilterCollection::class, [], [], '', false);

        $this->searchEngineResponse = new SearchEngineResponse(
            $this->stubSearchDocumentCollection,
            $this->stubFilterCollection
        );
    }

    public function testSearchDocumentCollectionIsReturned()
    {
        $this->assertSame($this->stubSearchDocumentCollection, $this->searchEngineResponse->getSearchDocuments());
    }

    public function testFilterCollectionIsReturned()
    {
        $this->assertSame($this->stubFilterCollection, $this->searchEngineResponse->getFilterCollection());
    }
}
