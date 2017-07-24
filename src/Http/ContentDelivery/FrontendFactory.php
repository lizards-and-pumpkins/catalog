<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodRouter;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\HttpRouter;
use LizardsAndPumpkins\Http\Routing\MetaSnippetBasedRouter;
use LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodHandler;
use LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\GenericPageBuilder;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation;
use LizardsAndPumpkins\ProductDetail\ContentDelivery\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class FrontendFactory implements Factory
{
    use FactoryTrait;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $snippetKeyGeneratorLocator;

    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
    }

    public function createMetaSnippetBasedRouter(): HttpRouter
    {
        $router = new MetaSnippetBasedRouter(
            $this->getMasterFactory()->createUrlToWebsiteMap(),
            $this->getMasterFactory()->createSnippetReader(),
            $this->getMasterFactory()->createContext()
        );

        $router->registerHandlerCallback(ProductDetailViewRequestHandler::CODE, function (string $metaJson) {
            return $this->createProductDetailViewRequestHandler($metaJson);
        });

        $router->registerHandlerCallback(ProductListingRequestHandler::CODE, function (string $metaJson) {
            return $this->createProductListingRequestHandler($metaJson);
        });

        $router->registerHandlerCallback(ProductSearchRequestHandler::CODE, function (string $metaJson) {
            return $this->createProductSearchRequestHandler($metaJson);
        });

        return $router;
    }

    public function createUnknownHttpRequestMethodRouter(): HttpRouter
    {
        return new UnknownHttpRequestMethodRouter($this->createUnknownHttpRequestMethodHandler());
    }

    public function createUnknownHttpRequestMethodHandler(): HttpRequestHandler
    {
        return new UnknownHttpRequestMethodHandler();
    }

    private function createProductDetailViewRequestHandler(string $metaJson): ProductDetailViewRequestHandler
    {
        return new ProductDetailViewRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $metaJson
        );
    }

    public function createProductListingRequestHandler(string $metaJson): ProductListingRequestHandler
    {
        return new ProductListingRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createProductListingFacetFiltersToIncludeInResult(),
            $this->getMasterFactory()->createUrlToWebsiteMap(),
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createProductListingPageRequest(),
            $this->getMasterFactory()->createProductSearchService(),
            $metaJson,
            $this->getMasterFactory()->getProductListingDefaultSortBy(),
            ...$this->getMasterFactory()->getProductListingAvailableSortBy()
        );
    }

    public function createProductSearchRequestHandler(string $metaJson): ProductSearchRequestHandler
    {
        return new ProductSearchRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createProductSearchFacetFiltersToIncludeInResult(),
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createProductListingPageRequest(),
            $this->getMasterFactory()->createProductSearchService(),
            $this->getMasterFactory()->createFullTextCriteriaBuilder(),
            $metaJson,
            $this->getMasterFactory()->getProductSearchDefaultSortBy(),
            ...$this->getMasterFactory()->getProductSearchAvailableSortBy()
        );
    }

    public function createProductListingFacetFiltersToIncludeInResult(): FacetFiltersToIncludeInResult
    {
        $facetFields = $this->getMasterFactory()->getProductListingFacetFilterRequestFields($this->createContext());
        return new FacetFiltersToIncludeInResult(...$facetFields);
    }

    public function createProductSearchFacetFiltersToIncludeInResult(): FacetFiltersToIncludeInResult
    {
        $facetFields = $this->getMasterFactory()->getProductSearchFacetFilterRequestFields($this->createContext());
        return new FacetFiltersToIncludeInResult(...$facetFields);
    }

    public function createProductListingPageContentBuilder(): ProductListingPageContentBuilder
    {
        return new ProductListingPageContentBuilder(
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->createSearchFieldToRequestParamMap($this->createContext()),
            $this->getMasterFactory()->getTranslatorRegistry()
        );
    }

    public function createProductListingPageRequest(): ProductListingPageRequest
    {
        return new ProductListingPageRequest(
            $this->getMasterFactory()->getProductsPerPageConfig(),
            $this->getMasterFactory()->createSearchFieldToRequestParamMap($this->createContext())
        );
    }

    public function createSnippetKeyGeneratorLocator(): SnippetKeyGeneratorLocator
    {
        return new CompositeSnippetKeyGeneratorLocatorStrategy(
            $this->getMasterFactory()->createContentBlockSnippetKeyGeneratorLocatorStrategy(),
            $this->getMasterFactory()->createRegistrySnippetKeyGeneratorLocatorStrategy()
        );
    }

    public function createRegistrySnippetKeyGeneratorLocatorStrategy(): RegistrySnippetKeyGeneratorLocatorStrategy
    {
        $registrySnippetKeyGeneratorLocator = new RegistrySnippetKeyGeneratorLocatorStrategy;
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailMetaSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailPageMetaSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailTemplateSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailTemplateSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductInListingSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductInListingSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingTemplateSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingTemplateSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            PriceSnippetRenderer::PRICE,
            function () {
                return $this->getMasterFactory()->createPriceSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            PriceSnippetRenderer::SPECIAL_PRICE,
            function () {
                return $this->getMasterFactory()->createSpecialPriceSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductSearchResultMetaSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductSearchResultMetaSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductJsonSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductJsonSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE,
            function () {
                return $this->getMasterFactory()->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE,
            function () {
                return $this->getMasterFactory()->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
            }
        );

        return $registrySnippetKeyGeneratorLocator;
    }

    public function getSnippetKeyGeneratorLocator(): SnippetKeyGeneratorLocator
    {
        if (is_null($this->snippetKeyGeneratorLocator)) {
            $this->snippetKeyGeneratorLocator = $this->createSnippetKeyGeneratorLocator();
        }

        return $this->snippetKeyGeneratorLocator;
    }

    public function createPageBuilder(): PageBuilder
    {
        $pageBuilder = new GenericPageBuilder(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator()
        );
        $this->registerSnippetTransformations($pageBuilder);

        return $pageBuilder;
    }

    private function registerSnippetTransformations(PageBuilder $pageBuilder)
    {
        $pageBuilder->registerSnippetTransformation(
            ProductJsonSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductJsonSnippetTransformation()
        );

        // Todo: remove when product detail page uses product json only
        $pageBuilder->registerSnippetTransformation(
            PriceSnippetRenderer::PRICE,
            $this->getMasterFactory()->createPriceSnippetTransformation()
        );

        // Todo: remove when product detail page uses product json only
        $pageBuilder->registerSnippetTransformation(
            PriceSnippetRenderer::SPECIAL_PRICE,
            $this->getMasterFactory()->createPriceSnippetTransformation()
        );

        // Todo: remove when product listing page uses ProductJsonService
        $pageBuilder->registerSnippetTransformation(
            'product_prices',
            $this->getMasterFactory()->createPricesJsonSnippetTransformation()
        );
    }

    public function createContext(): Context
    {
        /** @var ContextBuilder $contextBuilder */
        $contextBuilder = $this->getMasterFactory()->createContextBuilder();
        return $contextBuilder->createFromRequest($this->request);
    }

    public function createPriceSnippetTransformation(): SimpleEuroPriceSnippetTransformation
    {
        return new SimpleEuroPriceSnippetTransformation();
    }

    public function createPricesJsonSnippetTransformation(): PricesJsonSnippetTransformation
    {
        return new PricesJsonSnippetTransformation($this->getMasterFactory()->createPriceSnippetTransformation());
    }

    public function createProductJsonSnippetTransformation(): ProductJsonSnippetTransformation
    {
        return new ProductJsonSnippetTransformation($this->getMasterFactory()->createEnrichProductJsonWithPrices());
    }
}
