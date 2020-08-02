<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductId;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 */
class SearchDocumentTest extends TestCase
{
    public function testSearchDocumentIsCreated(): void
    {
        /** @var SearchDocumentFieldCollection|MockObject $stubDocumentFieldsCollection */
        $stubDocumentFieldsCollection = $this->createMock(SearchDocumentFieldCollection::class);

        /** @var Context|MockObject $testContext */
        $testContext = $this->createMock(Context::class);

        /** @var ProductId|MockObject $stubProductId */
        $stubProductId = $this->createMock(ProductId::class);

        $searchDocument = new SearchDocument($stubDocumentFieldsCollection, $testContext, $stubProductId);

        $this->assertSame($stubDocumentFieldsCollection, $searchDocument->getFieldsCollection());
        $this->assertSame($testContext, $searchDocument->getContext());
        $this->assertSame($stubProductId, $searchDocument->getProductId());
    }
}
