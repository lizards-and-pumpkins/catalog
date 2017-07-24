<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\SnippetReader;
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
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @method DataPoolWriter createDataPoolWriter
 * @method DataPoolReader createDataPoolReader
 * @method Context createContext
 * @method CommandQueue getCommandQueue
 * @method Queue getCommandMessageQueue
 * @method DomainEventQueue getEventQueue
 * @method Queue getEventMessageQueue
 * @method ContextSource createContextSource
 * @method ContextBuilder createContextBuilder
 * @method DomainEventConsumer createDomainEventConsumer
 * @method CommandConsumer createCommandConsumer
 * @method RegistrySnippetKeyGeneratorLocatorStrategy createRegistrySnippetKeyGeneratorLocatorStrategy
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method InMemoryLogger getLogger
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductStockQuantityRendererSnippetKeyGenerator
 * @method ContentBlockSnippetKeyGeneratorLocatorStrategy createContentBlockSnippetKeyGeneratorLocatorStrategy
 * @method GenericSnippetKeyGenerator createProductSearchResultMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingTemplateSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductDetailPageMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createContentBlockInProductListingSnippetKeyGenerator
 * @method string[] getRequiredContextParts
 * @method ProductXmlToProductBuilderLocator createProductXmlToProductBuilderLocator
 * @method string[] getSearchableAttributeCodes
 * @method FacetFiltersToIncludeInResult createProductListingFacetFiltersToIncludeInResult
 * @method ProductsPerPage getProductsPerPageConfig
 * @method SortBy[] getProductListingSortBy
 * @method SortBy[] getProductSearchSortBy
 * @method ProductListingRequestHandler createProductListingRequestHandler(string $metaJson)
 * @method ProductSearchRequestHandler createProductSearchRequestHandler(string $metaJson)
 * @method TaxableCountries createTaxableCountries
 * @method SearchEngine getSearchEngine
 * @method callable getProductDetailsViewTranslatorFactory
 * @method TranslatorRegistry getTranslatorRegistry
 * @method UrlToWebsiteMap createUrlToWebsiteMap
 * @method SnippetReader createSnippetReader
 */
class CatalogMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
