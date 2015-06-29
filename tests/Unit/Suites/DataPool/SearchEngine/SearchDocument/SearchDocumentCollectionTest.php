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

    public function testIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->searchDocumentCollection->getDocuments());
    }

    public function testSearchDocumentIsAddedToCollection()
    {
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->searchDocumentCollection->add($stubSearchDocument);
        $result = $this->searchDocumentCollection->getDocuments();

        $this->assertContainsOnly(SearchDocument::class, $result);
        $this->assertCount(1, $result);
        $this->assertSame($stubSearchDocument, $result[0]);
    }
}
