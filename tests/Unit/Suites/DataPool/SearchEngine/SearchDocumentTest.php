<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Environment\Environment;

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
        $stubEnvironment = $this->getMock(Environment::class);
        $payload = 'foo';

        $searchDocument = new SearchDocument($stubDocumentFieldsCollection, $stubEnvironment, $payload);

        $this->assertSame($stubDocumentFieldsCollection, $searchDocument->getFieldsCollection());
        $this->assertSame($stubEnvironment, $searchDocument->getEnvironment());
        $this->assertSame($payload, $searchDocument->getPayload());
    }
}
