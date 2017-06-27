<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
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
use LizardsAndPumpkins\Http\Routing\GenericHttpRouter;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRouter;
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
use LizardsAndPumpkins\Import\SnippetCode;

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

    public function createProductDetailViewRouter() : HttpRouter
    {
        return new GenericHttpRouter($this->createProductDetailViewRequestHandler());
    }

    public function createUnknownHttpRequestMethodRouter(): HttpRouter
    {
        return new GenericHttpRouter($this->createUnknownHttpRequestMethodHandler());
    }

    public function createUnknownHttpRequestMethodHandler(): HttpRequestHandler
    {
        return new UnknownHttpRequestMethodHandler();
    }

    public function createProductListingRouter() : HttpRouter
    {
        return new GenericHttpRouter($this->createProductListingRequestHandler());
    }

    private function createProductDetailViewRequestHandler() : ProductDetailViewRequestHandler
    {
        return new ProductDetailViewRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->createUrlToWebsiteMap(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createProductDetailPageMetaSnippetKeyGenerator()
        );
    }

    public function createProductListingRequestHandler() : ProductListingRequestHandler
    {
        return new ProductListingRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductListingSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductListingFacetFiltersToIncludeInResult(),
            $this->getMasterFactory()->createUrlToWebsiteMap(),
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createProductListingPageRequest(),
            $this->getMasterFactory()->createProductSearchService(),
            $this->getMasterFactory()->getProductListingDefaultSortBy(),
            ...$this->getMasterFactory()->getProductListingAvailableSortBy()
        );
    }

    public function createProductListingFacetFiltersToIncludeInResult() : FacetFiltersToIncludeInResult
    {
        $facetFields = $this->getMasterFactory()->getProductListingFacetFilterRequestFields($this->createContext());
        return new FacetFiltersToIncludeInResult(...$facetFields);
    }

    public function createProductSearchFacetFiltersToIncludeInResult() : FacetFiltersToIncludeInResult
    {
        $facetFields = $this->getMasterFactory()->getProductSearchFacetFilterRequestFields($this->createContext());
        return new FacetFiltersToIncludeInResult(...$facetFields);
    }

    public function createProductListingPageContentBuilder() : ProductListingPageContentBuilder
    {
        return new ProductListingPageContentBuilder(
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->createSearchFieldToRequestParamMap($this->createContext()),
            $this->getMasterFactory()->getTranslatorRegistry()
        );
    }

    public function createProductListingPageRequest() : ProductListingPageRequest
    {
        return new ProductListingPageRequest(
            $this->getMasterFactory()->getProductsPerPageConfig(),
            $this->getMasterFactory()->createSearchFieldToRequestParamMap($this->createContext())
        );
    }

    public function createSnippetKeyGeneratorLocator() : SnippetKeyGeneratorLocator
    {
        return new CompositeSnippetKeyGeneratorLocatorStrategy(
            $this->getMasterFactory()->createContentBlockSnippetKeyGeneratorLocatorStrategy(),
            $this->getMasterFactory()->createRegistrySnippetKeyGeneratorLocatorStrategy()
        );
    }

    public function createRegistrySnippetKeyGeneratorLocatorStrategy() : RegistrySnippetKeyGeneratorLocatorStrategy
    {
        $registrySnippetKeyGeneratorLocator = new RegistrySnippetKeyGeneratorLocatorStrategy;
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ProductDetailMetaSnippetRenderer::CODE),
            function () {
                return $this->getMasterFactory()->createProductDetailPageMetaSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ProductDetailTemplateSnippetRenderer::CODE),
            function () {
                return $this->getMasterFactory()->createProductDetailTemplateSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ProductInListingSnippetRenderer::CODE),
            function () {
                return $this->getMasterFactory()->createProductInListingSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ProductListingTemplateSnippetRenderer::CODE),
            function () {
                return $this->getMasterFactory()->createProductListingTemplateSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(PriceSnippetRenderer::PRICE),
            function () {
                return $this->getMasterFactory()->createPriceSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(PriceSnippetRenderer::SPECIAL_PRICE),
            function () {
                return $this->getMasterFactory()->createSpecialPriceSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ProductListingSnippetRenderer::CODE),
            function () {
                return $this->getMasterFactory()->createProductListingSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ProductSearchResultMetaSnippetRenderer::CODE),
            function () {
                return $this->getMasterFactory()->createProductSearchResultMetaSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ProductJsonSnippetRenderer::CODE),
            function () {
                return $this->getMasterFactory()->createProductJsonSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE),
            function () {
                return $this->getMasterFactory()->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            new SnippetCode(ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE),
            function () {
                return $this->getMasterFactory()->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
            }
        );

        return $registrySnippetKeyGeneratorLocator;
    }

    public function getSnippetKeyGeneratorLocator() : SnippetKeyGeneratorLocator
    {
        if (is_null($this->snippetKeyGeneratorLocator)) {
            $this->snippetKeyGeneratorLocator = $this->createSnippetKeyGeneratorLocator();
        }
        return $this->snippetKeyGeneratorLocator;
    }

    public function createPageBuilder() : PageBuilder
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
            new SnippetCode(ProductJsonSnippetRenderer::CODE),
            $this->getMasterFactory()->createProductJsonSnippetTransformation()
        );
    }

    public function createContext() : Context
    {
        /** @var ContextBuilder $contextBuilder */
        $contextBuilder = $this->getMasterFactory()->createContextBuilder();
        return $contextBuilder->createFromRequest($this->request);
    }

    public function createProductSearchResultRouter() : HttpRouter
    {
        return new GenericHttpRouter($this->createProductSearchRequestHandler());
    }

    public function createProductSearchRequestHandler() : ProductSearchRequestHandler
    {
        return new ProductSearchRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductSearchResultMetaSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductSearchFacetFiltersToIncludeInResult(),
            $this->getMasterFactory()->createUrlToWebsiteMap(),
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createProductListingPageRequest(),
            $this->getMasterFactory()->createProductSearchService(),
            $this->getMasterFactory()->createFullTextCriteriaBuilder(),
            $this->getMasterFactory()->getProductSearchDefaultSortBy(),
            ...$this->getMasterFactory()->getProductSearchAvailableSortBy()
        );
    }

    public function createPriceSnippetTransformation() : SimpleEuroPriceSnippetTransformation
    {
        return new SimpleEuroPriceSnippetTransformation();
    }

    public function createPricesJsonSnippetTransformation() : PricesJsonSnippetTransformation
    {
        return new PricesJsonSnippetTransformation($this->getMasterFactory()->createPriceSnippetTransformation());
    }

    public function createProductJsonSnippetTransformation() : ProductJsonSnippetTransformation
    {
        return new ProductJsonSnippetTransformation($this->getMasterFactory()->createEnrichProductJsonWithPrices());
    }
}
