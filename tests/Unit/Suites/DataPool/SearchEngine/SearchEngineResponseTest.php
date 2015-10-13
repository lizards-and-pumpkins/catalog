<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

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
     * @var SearchEngineFacetFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldCollection;

    /**
     * @var SearchEngineResponse
     */
    private $searchEngineResponse;

    protected function setUp()
    {
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $this->stubFacetFieldCollection = $this->getMock(SearchEngineFacetFieldCollection::class, [], [], '', false);

        $this->searchEngineResponse = new SearchEngineResponse(
            $this->stubSearchDocumentCollection,
            $this->stubFacetFieldCollection
        );
    }

    public function testSearchDocumentCollectionIsReturned()
    {
        $this->assertSame($this->stubSearchDocumentCollection, $this->searchEngineResponse->getSearchDocuments());
    }

    public function testSearchEngineFacetFieldCollectionIsReturned()
    {
        $this->assertSame($this->stubFacetFieldCollection, $this->searchEngineResponse->getFacetFieldCollection());
    }
}
