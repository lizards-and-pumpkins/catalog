<?php

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use LizardsAndPumpkins\Logging\InMemoryLogger;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @method DataPoolWriter createDataPoolWriter
 * @method DataPoolReader createDataPoolReader
 * @method CommandQueue getCommandQueue
 * @method Queue getCommandMessageQueue
 * @method DomainEventQueue getEventQueue
 * @method Queue getEventMessageQueue
 * @method Context getContext
 * @method ContextSource createContextSource
 * @method ContextBuilder createContextBuilder
 * @method DomainEventConsumer createDomainEventConsumer
 * @method CommandConsumer createCommandConsumer
 * @method RegistrySnippetKeyGeneratorLocatorStrategy createRegistrySnippetKeyGeneratorLocatorStrategy
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method InMemoryLogger getLogger
 * @method GenericSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductStockQuantityRendererSnippetKeyGenerator
 * @method ContentBlockSnippetKeyGeneratorLocatorStrategy createContentBlockSnippetKeyGeneratorLocatorStrategy
 * @method GenericSnippetKeyGenerator createProductSearchResultMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingTemplateSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductDetailPageMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createContentBlockInProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductInSearchAutosuggestionSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductSearchAutosuggestionMetaSnippetKeyGenerator
 * @method string[] getRequiredContextParts
 * @method ProductXmlToProductBuilderLocator createProductXmlToProductBuilderLocator
 * @method Context createContext
 * @method SearchCriteriaBuilder createSearchCriteriaBuilder
 * @method string[] getSearchableAttributeCodes
 * @method FacetFiltersToIncludeInResult createProductListingFacetFiltersToIncludeInResult
 * @method ProductsPerPage getProductsPerPageConfig
 * @method SortOrderConfig[] getProductListingSortOrderConfig
 * @method SortOrderConfig[] getProductSearchSortOrderConfig
 * @method SortOrderConfig getProductSearchAutosuggestionSortOrderConfig
 * @method ProductListingRequestHandler createProductListingRequestHandler
 * @method ProductSearchRequestHandler createProductSearchRequestHandler
 * @method TaxableCountries createTaxableCountries
 * @method SearchEngine getSearchEngine
 * @method callable getProductDetailsViewTranslatorFactory
 * @method TranslatorRegistry getTranslatorRegistry
 * @method SnippetKeyGenerator createProductListingTitleSnippetKeyGenerator
 */
class SampleMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
