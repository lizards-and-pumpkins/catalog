<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\ProductListing\ContentDelivery\SelectProductListingRobotsMetaTagContent;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
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
use LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer;
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

    public function createProductDetailViewRouter() : HttpRouter
    {
        return new GenericHttpRouter($this->createProductDetailViewRequestHandler());
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
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createSelectProductListingRobotsMetaTagContent(),
            $this->getMasterFactory()->createProductListingPageRequest()
        );
    }

    public function createSelectProductListingRobotsMetaTagContent() : SelectProductListingRobotsMetaTagContent
    {
        return new SelectProductListingRobotsMetaTagContent();
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
            $this->getMasterFactory()->createProductJsonService(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->createSearchFieldToRequestParamMap($this->createContext()),
            $this->getMasterFactory()->getTranslatorRegistry(),
            ...$this->getMasterFactory()->getProductListingSortBy()
        );
    }

    public function createProductListingPageRequest() : ProductListingPageRequest
    {
        return new ProductListingPageRequest(
            $this->getMasterFactory()->getProductsPerPageConfig(),
            $this->getMasterFactory()->createSearchFieldToRequestParamMap($this->createContext()),
            ...$this->getMasterFactory()->getProductListingSortBy()
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
            ProductDetailViewSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator();
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
            ProductListingSnippetRenderer::CANONICAL_TAG_KEY,
            function () {
                return $this->getMasterFactory()->createProductListingCanonicalTagSnippetKeyGenerator();
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
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailViewSnippetRenderer::TITLE_KEY_CODE,
            function () {
                return $this->getMasterFactory()->createProductTitleSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductCanonicalTagSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductCanonicalTagSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingTitleSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingTitleSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingDescriptionSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingDescriptionSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailViewSnippetRenderer::HTML_HEAD_META_CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailPageMetaDescriptionSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingSnippetRenderer::HTML_HEAD_META_KEY,
            function () {
                return $this->getMasterFactory()->createHtmlHeadMetaKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailPageRobotsMetaTagSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailPageRobotsMetaTagSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingRobotsMetaTagSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingPageRobotsMetaTagSnippetKeyGenerator();
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
        $pageBuilder = new PageBuilder(
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
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createProductListingPageRequest()
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
