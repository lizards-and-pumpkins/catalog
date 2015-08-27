<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 */
class SearchDocumentCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchDocumentCollection;

    protected function setUp()
    {
        $this->searchDocumentCollection = new SearchDocumentCollection();
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->searchDocumentCollection);
    }

    public function testIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->searchDocumentCollection->getDocuments());
    }

    public function testSearchDocumentIsAddedToCollection()
    {
        /** @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocument */
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->searchDocumentCollection->add($stubSearchDocument);
        $result = $this->searchDocumentCollection->getDocuments();

        $this->assertCount(1, $this->searchDocumentCollection);
        $this->assertContainsOnly(SearchDocument::class, $result);
        $this->assertSame($stubSearchDocument, $result[0]);
    }

    public function testSearchDocumentCollectionIsFilteredAccordingToSearchDocumentCriteria()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubSearchCriteria */
        $stubSearchCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);

        /** @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocumentA */
        $stubSearchDocumentA = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocumentA->method('isMatchingCriteria')->with($stubSearchCriteria)->willReturn(true);

        /** @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocumentB */
        $stubSearchDocumentB = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocumentB->method('isMatchingCriteria')->with($stubSearchCriteria)->willReturn(false);

        $this->searchDocumentCollection->add($stubSearchDocumentA);
        $this->searchDocumentCollection->add($stubSearchDocumentB);
        $filteredCollection = $this->searchDocumentCollection->getCollectionFilteredByCriteria($stubSearchCriteria);

        $result = $filteredCollection->getDocuments();

        $this->assertCount(1, $filteredCollection);
        $this->assertSame($stubSearchDocumentA, $result[0]);
    }
}
