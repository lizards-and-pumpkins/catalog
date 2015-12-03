<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Log\InMemoryLogger;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getCommandQueue
 * @method Queue\Queue getEventQueue
 * @method Context\Context getContext
 * @method Context\ContextSource createContextSource
 * @method Context\ContextBuilder createContextBuilder
 * @method DomainEventConsumer createDomainEventConsumer
 * @method CommandConsumer createCommandConsumer
 * @method SnippetKeyGeneratorLocator createRegistrySnippetKeyGeneratorLocatorStrategy
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
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
 * @method DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder createSearchCriteriaBuilder
 * @method string[] getSearchableAttributeCodes
 * @method array[] getProductListingFilterNavigationConfig
 * @method ContentDelivery\Catalog\ProductsPerPage getProductsPerPageConfig
 * @method ContentDelivery\Catalog\SortOrderConfig[] getProductListingSortOrderConfig
 * @method ContentDelivery\Catalog\SortOrderConfig[] getProductSearchSortOrderConfig
 * @method ContentDelivery\Catalog\SortOrderConfig getProductSearchAutosuggestionSortOrderConfig
 * @method ContentDelivery\Catalog\ProductListingRequestHandler createProductListingRequestHandler
 * @method ContentDelivery\Catalog\ProductSearchRequestHandler createProductSearchRequestHandler
 */
class SampleMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
