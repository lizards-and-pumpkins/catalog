<?php

namespace Brera\KeyValue;

use Brera\Environment\Environment;

/**
 * @covers \Brera\KeyValue\SearchDocument
 */
class SearchDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnCreateSearchDocument()
    {
        $stubDocumentFieldsCollection = $this->getMockBuilder(SearchDocumentFieldCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubEnvironment = $this->getMock(Environment::class);
        $payload = 'foo';

        $searchDocument = new SearchDocument($stubDocumentFieldsCollection, $stubEnvironment, $payload);

        $this->assertSame($stubDocumentFieldsCollection, $searchDocument->getFields());
        $this->assertSame($stubEnvironment, $searchDocument->getEnvironment());
        $this->assertSame($payload, $searchDocument->getPayload());
    }
}
