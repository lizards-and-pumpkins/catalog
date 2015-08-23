<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\InMemorySearchEngine
 * @covers \Brera\DataPool\SearchEngine\IntegrationTestSearchEngineAbstract
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\LocaleContextDecorator
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\WebsiteContextDecorator
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
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
}
