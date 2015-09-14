<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
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

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->searchDocumentCollection);
    }

    public function testCollectionIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->searchDocumentCollection);
    }

    public function testCollectionIsAccessibleViaGetter()
    {
        /** @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocument */
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->searchDocumentCollection->add($stubSearchDocument);
        $result = $this->searchDocumentCollection->getDocuments();

        $this->assertCount(1, $result);
        $this->assertContainsOnly(SearchDocument::class, $result);
        $this->assertSame($stubSearchDocument, $result[0]);
    }

    public function testCollectionIsAccessibleViaArrayIterator()
    {
        /** @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocument */
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->searchDocumentCollection->add($stubSearchDocument);

        $this->assertContains($stubSearchDocument, $this->searchDocumentCollection->getIterator());
    }
}
