<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument
 */
class SearchDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateSearchDocument()
    {
        $stubDocumentFieldsCollection = $this->getMockBuilder(SearchDocumentFieldCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubContext = $this->getMock(Context::class);
        $content = 'foo';

        $searchDocument = new SearchDocument($stubDocumentFieldsCollection, $stubContext, $content);

        $this->assertSame($stubDocumentFieldsCollection, $searchDocument->getFieldsCollection());
        $this->assertSame($stubContext, $searchDocument->getContext());
        $this->assertSame($content, $searchDocument->getContent());
    }
}
