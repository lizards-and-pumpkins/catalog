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
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method InMemoryLogger getLogger
 * @method GenericSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductStockQuantityRendererSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createContentBlockSnippetKeyGenerator
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
 */
class SampleMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
