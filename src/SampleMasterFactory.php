<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\RegistrySnippetKeyGeneratorLocatorStrategy;
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
 * @method RegistrySnippetKeyGeneratorLocatorStrategy createRegistrySnippetKeyGeneratorLocatorStrategy
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method Log\InMemoryLogger getLogger
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
 * @method FacetFiltersToIncludeInResult createProductListingFacetFiltersToIncludeInResult
 * @method ContentDelivery\Catalog\ProductsPerPage getProductsPerPageConfig
 * @method ContentDelivery\Catalog\SortOrderConfig[] getProductListingSortOrderConfig
 * @method ContentDelivery\Catalog\SortOrderConfig[] getProductSearchSortOrderConfig
 * @method ContentDelivery\Catalog\SortOrderConfig getProductSearchAutosuggestionSortOrderConfig
 * @method ContentDelivery\Catalog\ProductListingRequestHandler createProductListingRequestHandler
 * @method ContentDelivery\Catalog\ProductSearchRequestHandler createProductSearchRequestHandler
 * @method TwentyOneRunTaxableCountries createTaxableCountries
 * @method SearchEngine getSearchEngine
 */
class SampleMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
