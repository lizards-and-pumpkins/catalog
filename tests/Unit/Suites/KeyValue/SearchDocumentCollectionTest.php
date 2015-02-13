<?php

namespace Brera\KeyValue;

/**
 * @covers \Brera\KeyValue\SearchDocumentCollection
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

    /**
     * @test
     */
    public function itShouldBeEmpty()
    {
        $this->assertCount(0, $this->searchDocumentCollection->getDocuments());
    }

    /**
     * @test
     */
    public function itShouldAddSearchDocumentToCollection()
    {
        $stubSearchDocument = $this->getMockBuilder(SearchDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchDocumentCollection->add($stubSearchDocument);
        $result = $this->searchDocumentCollection->getDocuments();

        $this->assertContainsOnly(SearchDocument::class, $result);
        $this->assertCount(1, $result);
        $this->assertSame($stubSearchDocument, $result[0]);
    }
}
