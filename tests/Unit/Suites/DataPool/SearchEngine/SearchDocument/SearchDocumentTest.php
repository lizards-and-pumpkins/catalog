<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductId;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 */
class SearchDocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testSearchDocumentIsCreated()
    {
        /** @var SearchDocumentFieldCollection|\PHPUnit_Framework_MockObject_MockObject $stubDocumentFieldsCollection */
        $stubDocumentFieldsCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $testContext */
        $testContext = $this->getMock(Context::class);

        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $searchDocument = new SearchDocument($stubDocumentFieldsCollection, $testContext, $stubProductId);

        $this->assertSame($stubDocumentFieldsCollection, $searchDocument->getFieldsCollection());
        $this->assertSame($testContext, $searchDocument->getContext());
        $this->assertSame($stubProductId, $searchDocument->getProductId());
    }
}
