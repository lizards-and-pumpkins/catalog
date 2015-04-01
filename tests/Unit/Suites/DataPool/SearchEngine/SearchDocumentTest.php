<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocumentField
 */
class SearchDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDocumentFieldsCollection;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var string
     */
    private $content = 'foo';

    /**
     * @var SearchDocument
     */
    private $searchDocument;

    /**
     * @return array
     */
    protected function setUp()
    {
        $this->stubDocumentFieldsCollection = $this->getMockBuilder(SearchDocumentFieldCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubContext = $this->getMock(Context::class);

        $this->searchDocument = new SearchDocument(
            $this->stubDocumentFieldsCollection,
            $this->stubContext,
            $this->content
        );
    }

    /**
     * @test
     */
    public function itShouldCreateSearchDocument()
    {
        $this->assertSame($this->stubDocumentFieldsCollection, $this->searchDocument->getFieldsCollection());
        $this->assertSame($this->stubContext, $this->searchDocument->getContext());
        $this->assertSame($this->content, $this->searchDocument->getContent());
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfInputArrayIsEmpty()
    {
        $this->assertFalse($this->searchDocument->hasFieldMatchingOneOf([]));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfNoMatchingFieldIsPresent()
    {
        $this->assertFalse($this->searchDocument->hasFieldMatchingOneOf(['field-name' => 'field-value']));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfAMatchingFieldIsPresent()
    {
        $this->stubDocumentFieldsCollection->expects($this->once())->method('contains')
            ->with($this->isInstanceOf(SearchDocumentField::class))
            ->willReturn(true);
        $this->assertTrue($this->searchDocument->hasFieldMatchingOneOf(['field-name' => 'field-value']));
    }
}
