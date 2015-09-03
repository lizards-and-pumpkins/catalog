<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

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
}
