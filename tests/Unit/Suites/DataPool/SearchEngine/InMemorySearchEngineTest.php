<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Product\ProductId;
use Brera\Utils\Clearable;

/**
 * @covers \Brera\DataPool\SearchEngine\InMemorySearchEngine
 * @covers \Brera\DataPool\SearchEngine\IntegrationTestSearchEngineAbstract
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\LocaleContextDecorator
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\WebsiteContextDecorator
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Product\ProductId
 */
class InMemorySearchEngineTest extends AbstractSearchEngineTest
{
    /**
     * @return SearchEngine
     */
    protected function createSearchEngineInstance()
    {
        return new InMemorySearchEngine();
    }

    public function testItIsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->getSearchEngine());
    }

    public function testItClearsTheStorage()
    {
        $searchDocumentFieldName = 'foo';
        $searchDocumentFieldValue = 'bar';
        $productId = ProductId::fromString('id');

        $searchDocument = $this->createSearchDocument(
            [$searchDocumentFieldName => $searchDocumentFieldValue],
            $productId
        );

        $this->getSearchEngine()->addSearchDocument($searchDocument);
        $this->getSearchEngine()->clear();
        $result = $this->getSearchEngine()->query($searchDocumentFieldValue, $this->getTestContext());

        $this->assertEmpty($result);
    }
}
