<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Log\InMemoryLogger;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getCommandQueue
 * @method Queue\Queue getEventQueue
 * @method Context\Context getContext
 * @method Context\ContextSource createContextSource
 * @method DomainEventConsumer createDomainEventConsumer
 * @method CommandConsumer createCommandConsumer
 * @method RegistrySnippetKeyGeneratorLocatorStrategy createRegistrySnippetKeyGeneratorLocatorStrategy
 * @method InMemoryLogger getLogger
 * @method GenericSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductStockQuantityRendererSnippetKeyGenerator
 * @method ContentBlockSnippetKeyGeneratorLocatorStrategy createContentBlockSnippetKeyGeneratorLocatorStrategy
 * @method GenericSnippetKeyGenerator createProductSearchResultMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingCriteriaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductDetailPageMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createContentBlockInProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductInSearchAutosuggestionSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductSearchAutosuggestionMetaSnippetKeyGenerator
 * @method string[] getRequiredContexts
 * @method Projection\Catalog\Import\ProductXmlToProductBuilderLocator createProductXmlToProductBuilderLocator
 * @method Context\Context createContext
 * @method Product\FilterNavigationFilterCollection createFilterNavigationFilterCollection
 * @method DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder createSearchCriteriaBuilder
 */
class SampleMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
