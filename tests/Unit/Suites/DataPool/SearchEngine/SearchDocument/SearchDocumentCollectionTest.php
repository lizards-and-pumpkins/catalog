<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 */
class SearchDocumentCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, new SearchDocumentCollection());
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, new SearchDocumentCollection());
    }

    public function testCollectionWithoutArgumentsIsEmpty()
    {
        $this->assertCount(0, new SearchDocumentCollection());
    }

    public function testCollectionIsAccessibleViaGetter()
    {
        /** @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocument */
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $searchDocumentCollection = new SearchDocumentCollection($stubSearchDocument);
        $result = $searchDocumentCollection->getDocuments();

        $this->assertCount(1, $result);
        $this->assertContainsOnly(SearchDocument::class, $result);
        $this->assertSame($stubSearchDocument, $result[0]);
    }

    public function testCollectionIsAccessibleViaArrayIterator()
    {
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $searchDocumentCollection = new SearchDocumentCollection($stubSearchDocument);

        $this->assertContains($stubSearchDocument, $searchDocumentCollection->getIterator());
    }
}
