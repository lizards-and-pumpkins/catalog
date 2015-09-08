<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\VersionedContext;
use Brera\DataVersion;
use Brera\Product\ProductId;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextBuilder
 */
class SearchDocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testSearchDocumentIsCreated()
    {
        /** @var SearchDocumentFieldCollection|\PHPUnit_Framework_MockObject_MockObject $stubDocumentFieldsCollection */
        $stubDocumentFieldsCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);

        $testContext = new VersionedContext(DataVersion::fromVersionString('123'));

        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $searchDocument = new SearchDocument($stubDocumentFieldsCollection, $testContext, $stubProductId);

        $this->assertSame($stubDocumentFieldsCollection, $searchDocument->getFieldsCollection());
        $this->assertSame($testContext, $searchDocument->getContext());
        $this->assertSame($stubProductId, $searchDocument->getProductId());
    }
}
