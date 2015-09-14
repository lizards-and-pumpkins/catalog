<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTestSearchEngineAbstract
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\ContextDecorator
 * @uses   \LizardsAndPumpkins\Context\LocaleContextDecorator
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 * @uses   \LizardsAndPumpkins\Context\WebsiteContextDecorator
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Product\ProductId
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
