<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\BaseUrl\WebsiteBaseUrlBuilder;
use LizardsAndPumpkins\Content\ContentBlockProjector;
use LizardsAndPumpkins\Content\ContentBlockSnippetRenderer;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextCountry as CountryContextPartBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale as LocaleContextPartBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextVersion as VersionContextPartBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite as WebsiteContextPartBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Exception\UndefinedFactoryMethodException;
use LizardsAndPumpkins\Http\HttpRouterChain;
use LizardsAndPumpkins\Http\ResourceNotFoundRouter;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductDetailViewBlockRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionBlockRenderer;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingDescriptionBlockRenderer;
use LizardsAndPumpkins\Product\ProductListingDescriptionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingTemplateProjector;
use LizardsAndPumpkins\Product\ProductListingTitleSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearch\ConfigurableProductAttributeValueCollector;
use LizardsAndPumpkins\Product\ProductSearch\DefaultAttributeValueCollector;
use LizardsAndPumpkins\Product\ProductSearch\AttributeValueCollectorLocator;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionBlockRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionTemplateProjector;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductListingBlockRenderer;
use LizardsAndPumpkins\Product\ProductListingSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingSnippetProjector;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductImportCommandLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductListingImportCommandLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\Product\ProductProjector;
use LizardsAndPumpkins\Product\ProductListingBuilder;
use LizardsAndPumpkins\Product\ProductSearch\ProductSearchDocumentBuilder;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\QueueImportCommands;
use LizardsAndPumpkins\Projection\Catalog\Import\SimpleProductXmlToProductBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToProductBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilder;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\AddProductListingCommandHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport;
use LizardsAndPumpkins\Projection\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\Projection\TemplateProjectorLocator;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Renderer\BlockStructure;
use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\CsvTranslator;
use LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\CompositeSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Utils\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Utils\ImageStorage\MediaBaseUrlBuilder;
use LizardsAndPumpkins\Utils\ImageStorage\MediaDirectoryBaseUrlBuilder;
use LizardsAndPumpkins\Website\ConfigurableHostToWebsiteMap;
use LizardsAndPumpkins\Website\HostToWebsiteMap;

class CommonFactory implements Factory, DomainEventHandlerFactory, CommandHandlerFactory
{
    use FactoryTrait;

    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var Queue
     */
    private $eventQueue;

    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var ImageProcessorCollection
     */
    private $imageProcessorCollection;

    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;

    /**
     * @var TranslatorRegistry
     */
    private $translatorRegistry;

    /**
     * @var string
     */
    private $currentDataVersion;

    /**
     * @var FacetFieldTransformationRegistry
     */
    private $memoizedFacetFieldTransformationRegistry;

    /**
     * @param ProductWasUpdatedDomainEvent $event
     * @return ProductWasUpdatedDomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(ProductWasUpdatedDomainEvent $event)
    {
        return new ProductWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createProductProjector()
        );
    }

    /**
     * @param TemplateWasUpdatedDomainEvent $event
     * @return TemplateWasUpdatedDomainEventHandler
     */
    public function createTemplateWasUpdatedDomainEventHandler(TemplateWasUpdatedDomainEvent $event)
    {
        return new TemplateWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->createTemplateProjectorLocator()
        );
    }

    /**
     * @return TemplateProjectorLocator
     */
    public function createTemplateProjectorLocator()
    {
        $templateProjectorLocator = new TemplateProjectorLocator();
        $templateProjectorLocator->register(
            ProductListingTemplateSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductListingTemplateProjector()
        );
        $templateProjectorLocator->register(
            ProductSearchAutosuggestionSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductSearchAutosuggestionTemplateProjector()
        );

        return $templateProjectorLocator;
    }

    /**
     * @param ProductListingWasAddedDomainEvent $event
     * @return ProductListingWasAddedDomainEventHandler
     */
    public function createProductListingWasAddedDomainEventHandler(ProductListingWasAddedDomainEvent $event)
    {
        return new ProductListingWasAddedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createProductListingSnippetProjector()
        );
    }

    /**
     * @return ProductListingBuilder
     */
    public function createProductListingBuilder()
    {
        return new ProductListingBuilder();
    }

    /**
     * @return ProductProjector
     */
    public function createProductProjector()
    {
        return new ProductProjector(
            $this->getMasterFactory()->createProductViewLocator(),
            $this->getMasterFactory()->createProductSnippetRendererCollection(),
            $this->getMasterFactory()->createProductSearchDocumentBuilder(),
            $this->createUrlKeyForContextCollector(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    /**
     * @return UrlKeyForContextCollector
     */
    public function createUrlKeyForContextCollector()
    {
        return new UrlKeyForContextCollector(
            $this->createContextSource()
        );
    }

    /**
     * @return SnippetRendererCollection
     */
    public function createProductSnippetRendererCollection()
    {
        return new SnippetRendererCollection(
            $this->createProductSnippetRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    private function createProductSnippetRendererList()
    {
        return [
            $this->getMasterFactory()->createProductDetailViewSnippetRenderer(),
            $this->getMasterFactory()->createProductInListingSnippetRenderer(),
            $this->getMasterFactory()->createProductInSearchAutosuggestionSnippetRenderer(),
            $this->getMasterFactory()->createPriceSnippetRenderer(),
            $this->getMasterFactory()->createSpecialPriceSnippetRenderer(),
            $this->getMasterFactory()->createProductJsonSnippetRenderer(),
            $this->getMasterFactory()->createConfigurableProductJsonSnippetRenderer(),
        ];
    }

    /**
     * @return ProductJsonSnippetRenderer
     */
    public function createProductJsonSnippetRenderer()
    {
        return new ProductJsonSnippetRenderer(
            $this->getMasterFactory()->createProductJsonSnippetKeyGenerator()
        );
    }

    /**
     * @return GenericSnippetKeyGenerator
     */
    public function createProductJsonSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            ProductJsonSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ConfigurableProductJsonSnippetRenderer
     */
    public function createConfigurableProductJsonSnippetRenderer()
    {
        return new ConfigurableProductJsonSnippetRenderer(
            $this->getMasterFactory()->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createConfigurableProductVariationAttributesJsonSnippetKeyGenerator()
    {
        $usedDataParts = ['product_id'];

        return new GenericSnippetKeyGenerator(
            ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator()
    {
        $usedDataParts = ['product_id'];

        return new GenericSnippetKeyGenerator(
            ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductSearchAutosuggestionTemplateProjector
     */
    public function createProductSearchAutosuggestionTemplateProjector()
    {
        return new ProductSearchAutosuggestionTemplateProjector(
            $this->getMasterFactory()->createDataPoolWriter(),
            $this->createProductSearchAutosuggestionTemplateRendererCollection()
        );
    }

    /**
     * @return SnippetRendererCollection
     */
    private function createProductSearchAutosuggestionTemplateRendererCollection()
    {
        return new SnippetRendererCollection(
            $this->createProductSearchAutosuggestionRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    private function createProductSearchAutosuggestionRendererList()
    {
        return [
            $this->getMasterFactory()->createProductSearchAutosuggestionSnippetRenderer(),
            $this->getMasterFactory()->createProductSearchAutosuggestionMetaSnippetRenderer(),
        ];
    }

    /**
     * @return ProductSearchAutosuggestionSnippetRenderer
     */
    public function createProductSearchAutosuggestionSnippetRenderer()
    {
        return new ProductSearchAutosuggestionSnippetRenderer(
            $this->getMasterFactory()->createProductSearchAutosuggestionSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductSearchAutosuggestionBlockRenderer(),
            $this->getMasterFactory()->createContextSource()
        );
    }

    /**
     * @return ProductSearchAutosuggestionMetaSnippetRenderer
     */
    public function createProductSearchAutosuggestionMetaSnippetRenderer()
    {
        return new ProductSearchAutosuggestionMetaSnippetRenderer(
            $this->getMasterFactory()->createProductSearchAutosuggestionMetaSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductSearchAutosuggestionBlockRenderer(),
            $this->getMasterFactory()->createContextSource()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductSearchAutosuggestionSnippetKeyGenerator()
    {
        $usedDataParts = [];

        return new GenericSnippetKeyGenerator(
            ProductSearchAutosuggestionSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductSearchAutosuggestionMetaSnippetKeyGenerator()
    {
        $usedDataParts = [];

        return new GenericSnippetKeyGenerator(
            ProductSearchAutosuggestionMetaSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductSearchAutosuggestionBlockRenderer
     */
    public function createProductSearchAutosuggestionBlockRenderer()
    {
        return new ProductSearchAutosuggestionBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createBaseUrlBuilder()
        );
    }

    /**
     * @return ProductListingTemplateProjector
     */
    public function createProductListingTemplateProjector()
    {
        return new ProductListingTemplateProjector(
            $this->createProductListingTemplateRendererCollection(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    /**
     * @return SnippetRendererCollection
     */
    private function createProductListingTemplateRendererCollection()
    {
        return new SnippetRendererCollection(
            $this->createProductListingTemplateRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    private function createProductListingTemplateRendererList()
    {
        return [
            $this->getMasterFactory()->createProductListingTemplateSnippetRenderer(),
            $this->getMasterFactory()->createProductSearchResultMetaSnippetRenderer(),
        ];
    }

    /**
     * @return ProductListingTemplateSnippetRenderer
     */
    public function createProductListingTemplateSnippetRenderer()
    {
        return new ProductListingTemplateSnippetRenderer(
            $this->getMasterFactory()->createProductListingTemplateSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductListingBlockRenderer(),
            $this->getMasterFactory()->createContextSource()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductListingTemplateSnippetKeyGenerator()
    {
        $usedDataParts = [];

        return new GenericSnippetKeyGenerator(
            ProductListingTemplateSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductListingBlockRenderer
     */
    public function createProductListingBlockRenderer()
    {
        return new ProductListingBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createBaseUrlBuilder()
        );
    }

    /**
     * @return ProductListingSnippetProjector
     */
    public function createProductListingSnippetProjector()
    {
        return new ProductListingSnippetProjector(
            $this->getMasterFactory()->createProductListingSnippetRendererCollection(),
            $this->getMasterFactory()->createUrlKeyForContextCollector(),
            $this->getMasterFactory()->createDataPoolWriter(),
            $this->getMasterFactory()->createContextSource()
        );
    }

    /**
     * @return SnippetRendererCollection
     */
    public function createProductListingSnippetRendererCollection()
    {
        return new SnippetRendererCollection(
            $this->createProductListingSnippetRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createProductListingSnippetRendererList()
    {
        return [
            $this->getMasterFactory()->createProductListingSnippetRenderer(),
            $this->getMasterFactory()->createProductListingTitleSnippetRenderer(),
            $this->getMasterFactory()->createProductListingDescriptionSnippetRenderer(),
        ];
    }

    /**
     * @return ProductListingTitleSnippetRenderer
     */
    public function createProductListingTitleSnippetRenderer()
    {
        return new ProductListingTitleSnippetRenderer(
            $this->getMasterFactory()->createProductListingTitleSnippetKeyGenerator(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    /**
     * @return GenericSnippetKeyGenerator
     */
    public function createProductListingTitleSnippetKeyGenerator()
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            ProductListingTitleSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductListingSnippetRenderer
     */
    public function createProductListingSnippetRenderer()
    {
        return new ProductListingSnippetRenderer(
            $this->getMasterFactory()->createProductListingBlockRenderer(),
            $this->getMasterFactory()->createProductListingSnippetKeyGenerator(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductListingSnippetKeyGenerator()
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            ProductListingSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductDetailViewSnippetRenderer
     */
    public function createProductDetailViewSnippetRenderer()
    {
        return new ProductDetailViewSnippetRenderer(
            $this->getMasterFactory()->createProductDetailViewBlockRenderer(),
            $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductTitleSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductDetailPageMetaSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductDetailPageMetaDescriptionSnippetKeyGenerator()
        );
    }

    /**
     * @return ProductDetailViewBlockRenderer
     */
    public function createProductDetailViewBlockRenderer()
    {
        return new ProductDetailViewBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createBaseUrlBuilder()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductDetailViewSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            'product_detail_view_content',
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductTitleSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            'product_title',
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductDetailPageMetaSnippetKeyGenerator()
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            ProductDetailViewSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductInListingSnippetRenderer
     */
    public function createProductInListingSnippetRenderer()
    {
        return new ProductInListingSnippetRenderer(
            $this->getMasterFactory()->createProductInListingSnippetKeyGenerator()
        );
    }

    /**
     * @return ProductInSearchAutosuggestionSnippetRenderer
     */
    public function createProductInSearchAutosuggestionSnippetRenderer()
    {
        return new ProductInSearchAutosuggestionSnippetRenderer(
            $this->getMasterFactory()->createProductInSearchAutosuggestionBlockRenderer(),
            $this->getMasterFactory()->createProductInSearchAutosuggestionSnippetKeyGenerator()
        );
    }

    /**
     * @return PriceSnippetRenderer
     */
    public function createPriceSnippetRenderer()
    {
        $productRegularPriceAttributeCode = 'price';

        return new PriceSnippetRenderer(
            $this->getMasterFactory()->createTaxableCountries(),
            $this->getMasterFactory()->createTaxServiceLocator(),
            $this->getMasterFactory()->createPriceSnippetKeyGenerator(),
            $this->createContextBuilder(),
            $productRegularPriceAttributeCode
        );
    }

    /**
     * @return PriceSnippetRenderer
     */
    public function createSpecialPriceSnippetRenderer()
    {
        $productSpecialPriceAttributeCode = 'special_price';

        return new PriceSnippetRenderer(
            $this->getMasterFactory()->createTaxableCountries(),
            $this->getMasterFactory()->createTaxServiceLocator(),
            $this->getMasterFactory()->createSpecialPriceSnippetKeyGenerator(),
            $this->createContextBuilder(),
            $productSpecialPriceAttributeCode
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductInListingSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            ProductInListingSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductInSearchAutosuggestionBlockRenderer
     */
    public function createProductInSearchAutosuggestionBlockRenderer()
    {
        return new ProductInSearchAutosuggestionBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createBaseUrlBuilder()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductInSearchAutosuggestionSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            ProductInSearchAutosuggestionSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createPriceSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            PriceSnippetRenderer::PRICE,
            $this->getPriceSnippetKeyContextPartCodes(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createSpecialPriceSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            PriceSnippetRenderer::SPECIAL_PRICE,
            $this->getPriceSnippetKeyContextPartCodes(),
            $usedDataParts
        );
    }

    /**
     * @return string[]
     */
    private function getPriceSnippetKeyContextPartCodes()
    {
        return [WebsiteContextPartBuilder::CODE, CountryContextPartBuilder::CODE];
    }

    /**
     * @param string $snippetCode
     * @return SnippetKeyGenerator
     */
    public function createContentBlockSnippetKeyGenerator($snippetCode)
    {
        $usedDataParts = [];

        return new GenericSnippetKeyGenerator(
            $snippetCode,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @param string $snippetCode
     * @return SnippetKeyGenerator
     */
    public function createProductListingContentBlockSnippetKeyGenerator($snippetCode)
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            $snippetCode,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGeneratorLocator
     */
    public function createContentBlockSnippetKeyGeneratorLocatorStrategy()
    {
        return new CompositeSnippetKeyGeneratorLocatorStrategy(
            new ContentBlockSnippetKeyGeneratorLocatorStrategy(function ($snippetCode) {
                return $this->getMasterFactory()->createContentBlockSnippetKeyGenerator($snippetCode);
            }),
            new ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy(function ($snippetCode) {
                return $this->getMasterFactory()->createProductListingContentBlockSnippetKeyGenerator($snippetCode);
            })
        );
    }

    /**
     * @return BlockStructure
     */
    public function createBlockStructure()
    {
        return new BlockStructure();
    }

    /**
     * @return ProductXmlToProductBuilderLocator
     */
    public function createProductXmlToProductBuilderLocator()
    {
        $productXmlToProductTypeBuilders = $this->getMasterFactory()->createProductXmlToProductTypeBuilders();
        return new ProductXmlToProductBuilderLocator(...$productXmlToProductTypeBuilders);
    }

    /**
     * @return ProductXmlToProductBuilder[]
     */
    public function createProductXmlToProductTypeBuilders()
    {
        return [
            $this->getMasterFactory()->createSimpleProductXmlToProductBuilder(),
            $this->getMasterFactory()->createConfigurableProductXmlToProductBuilder(),
        ];
    }

    /**
     * @return SimpleProductXmlToProductBuilder
     */
    public function createSimpleProductXmlToProductBuilder()
    {
        return new SimpleProductXmlToProductBuilder();
    }

    /**
     * @return ConfigurableProductXmlToProductBuilder
     */
    public function createConfigurableProductXmlToProductBuilder()
    {
        $productTypeBuilderFactoryProxy = $this->getMasterFactory()
            ->createProductXmlToProductBuilderLocatorProxyFactoryMethod();
        return new ConfigurableProductXmlToProductBuilder($productTypeBuilderFactoryProxy);
    }

    /**
     * @return \Closure
     */
    public function createProductXmlToProductBuilderLocatorProxyFactoryMethod()
    {
        return function () {
            return $this->createProductXmlToProductBuilderLocator();
        };
    }

    /**
     * @return ThemeLocator
     */
    public function createThemeLocator()
    {
        return ThemeLocator::fromPath($this->getMasterFactory()->getBasePathConfig());
    }

    /**
     * @return string
     */
    public function getBasePathConfig()
    {
        return dirname(__DIR__);
    }

    /**
     * @return ContextSource
     */
    public function createContextSource()
    {
        return new TwentyOneRunContextSource($this->getMasterFactory()->createContextBuilder());
    }

    /**
     * @return ContextBuilder
     */
    public function createContextBuilder()
    {
        return new SelfContainedContextBuilder(
            $this->getMasterFactory()->createVersionContextPartBuilder(),
            $this->getMasterFactory()->createWebsiteContextPartBuilder(),
            $this->getMasterFactory()->createCountryContextPartBuilder(),
            $this->getMasterFactory()->createLocaleContextPartBuilder()
        );
    }

    /**
     * @return VersionContextPartBuilder
     */
    public function createVersionContextPartBuilder()
    {
        $dataVersion = $this->getCurrentDataVersion();
        return new VersionContextPartBuilder(DataVersion::fromVersionString($dataVersion));
    }

    /**
     * @return WebsiteContextPartBuilder
     */
    public function createWebsiteContextPartBuilder()
    {
        return new WebsiteContextPartBuilder($this->getMasterFactory()->createHostToWebsiteMap());
    }

    /**
     * @return LocaleContextPartBuilder
     */
    public function createLocaleContextPartBuilder()
    {
        return new LocaleContextPartBuilder();
    }

    /**
     * @return CountryContextPartBuilder
     */
    public function createCountryContextPartBuilder()
    {
        return new CountryContextPartBuilder($this->getMasterFactory()->createWebsiteToCountryMap());
    }

    /**
     * @return HostToWebsiteMap
     */
    public function createHostToWebsiteMap()
    {
        return ConfigurableHostToWebsiteMap::fromConfig($this->getMasterFactory()->createConfigReader());
    }

    /**
     * @return string
     */
    private function getCurrentDataVersion()
    {
        if (null === $this->currentDataVersion) {
            /** @var DataPoolReader $dataPoolReader */
            $dataPoolReader = $this->getMasterFactory()->createDataPoolReader();
            $this->currentDataVersion = $dataPoolReader->getCurrentDataVersion();
        }

        return $this->currentDataVersion;
    }

    /**
     * @return DomainEventHandlerLocator
     */
    public function createDomainEventHandlerLocator()
    {
        return new DomainEventHandlerLocator($this->getMasterFactory());
    }

    /**
     * @return DataPoolWriter
     */
    public function createDataPoolWriter()
    {
        return new DataPoolWriter(
            $this->getMasterFactory()->getKeyValueStore(),
            $this->getMasterFactory()->getSearchEngine(),
            $this->getMasterFactory()->getUrlKeyStore()
        );
    }

    /**
     * @return KeyValueStore
     */
    public function getKeyValueStore()
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->callExternalCreateMethod('KeyValueStore');
        }

        return $this->keyValueStore;
    }

    /**
     * @return DomainEventConsumer
     */
    public function createDomainEventConsumer()
    {
        return new DomainEventConsumer(
            $this->getMasterFactory()->getEventQueue(),
            $this->getMasterFactory()->createDomainEventHandlerLocator(),
            $this->getLogger()
        );
    }

    /**
     * @return Queue
     */
    public function getEventQueue()
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->callExternalCreateMethod('EventQueue');
        }

        return $this->eventQueue;
    }

    /**
     * @return DataPoolReader
     */
    public function createDataPoolReader()
    {
        return new DataPoolReader(
            $this->getMasterFactory()->getKeyValueStore(),
            $this->getMasterFactory()->getSearchEngine(),
            $this->getMasterFactory()->getUrlKeyStore()
        );
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = $this->callExternalCreateMethod('Logger');
        }

        return $this->logger;
    }

    /**
     * @param string $targetObjectName
     * @return object
     */
    private function callExternalCreateMethod($targetObjectName)
    {
        try {
            $instance = $this->getMasterFactory()->{'create' . $targetObjectName}();
        } catch (UndefinedFactoryMethodException $e) {
            throw new UndefinedFactoryMethodException(
                sprintf('Unable to create %s. Is the factory registered? %s', $targetObjectName, $e->getMessage())
            );
        }

        return $instance;
    }

    /**
     * @return ResourceNotFoundRouter
     */
    public function createResourceNotFoundRouter()
    {
        return new ResourceNotFoundRouter();
    }

    /**
     * @return HttpRouterChain
     */
    public function createHttpRouterChain()
    {
        return new HttpRouterChain();
    }

    /**
     * @return ProductSearchDocumentBuilder
     */
    public function createProductSearchDocumentBuilder()
    {
        $indexAttributeCodes = array_unique(array_merge(
            $this->getMasterFactory()->getSearchableAttributeCodes(),
            $this->getMasterFactory()->getFacetFilterRequestFieldCodesForSearchDocuments(),
            $this->getMasterFactory()->getAdditionalAttributesForSearchIndex()
        ));

        return new ProductSearchDocumentBuilder(
            $indexAttributeCodes,
            $this->getMasterFactory()->createAttributeValueCollectorLocator(),
            $this->getMasterFactory()->createTaxableCountries(),
            $this->getMasterFactory()->createTaxServiceLocator()
        );
    }

    /**
     * @return SearchEngine
     */
    public function getSearchEngine()
    {
        if (null === $this->searchEngine) {
            $this->searchEngine = $this->callExternalCreateMethod('SearchEngine');
        }

        return $this->searchEngine;
    }

    /**
     * @param ImageWasAddedDomainEvent $event
     * @return ImageWasAddedDomainEventHandler
     */
    public function createImageWasAddedDomainEventHandler(ImageWasAddedDomainEvent $event)
    {
        return new ImageWasAddedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createImageProcessorCollection()
        );
    }

    /**
     * @return ImageProcessorCollection
     */
    public function getImageProcessorCollection()
    {
        if (null === $this->imageProcessorCollection) {
            $this->imageProcessorCollection = $this->callExternalCreateMethod('ImageProcessorCollection');
        }

        return $this->imageProcessorCollection;
    }

    /**
     * @return CommandConsumer
     */
    public function createCommandConsumer()
    {
        return new CommandConsumer(
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->createCommandHandlerLocator(),
            $this->getLogger()
        );
    }

    /**
     * @return Queue
     */
    public function getCommandQueue()
    {
        if (null === $this->commandQueue) {
            $this->commandQueue = $this->callExternalCreateMethod('CommandQueue');
        }

        return $this->commandQueue;
    }

    /**
     * @return CommandHandlerLocator
     */
    public function createCommandHandlerLocator()
    {
        return new CommandHandlerLocator($this->getMasterFactory());
    }

    /**
     * @param UpdateContentBlockCommand $command
     * @return UpdateContentBlockCommandHandler
     */
    public function createUpdateContentBlockCommandHandler(UpdateContentBlockCommand $command)
    {
        return new UpdateContentBlockCommandHandler(
            $command,
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @param ContentBlockWasUpdatedDomainEvent $event
     * @return ContentBlockWasUpdatedDomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(ContentBlockWasUpdatedDomainEvent $event)
    {
        return new ContentBlockWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createContentBlockProjector()
        );
    }

    /**
     * @return ContentBlockProjector
     */
    public function createContentBlockProjector()
    {
        return new ContentBlockProjector(
            $this->getMasterFactory()->createContentBlockSnippetRendererCollection(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    /**
     * @return SnippetRendererCollection
     */
    public function createContentBlockSnippetRendererCollection()
    {
        return new SnippetRendererCollection(
            $this->getMasterFactory()->createContentBlockSnippetRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createContentBlockSnippetRendererList()
    {
        return [$this->getMasterFactory()->createContentBlockSnippetRenderer()];
    }

    /**
     * @return ContentBlockSnippetRenderer
     */
    public function createContentBlockSnippetRenderer()
    {
        return new ContentBlockSnippetRenderer(
            $this->getMasterFactory()->createContentBlockSnippetKeyGeneratorLocatorStrategy(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    /**
     * @param UpdateProductCommand $command
     * @return UpdateProductCommandHandler
     */
    public function createUpdateProductCommandHandler(UpdateProductCommand $command)
    {
        return new UpdateProductCommandHandler(
            $command,
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @param AddProductListingCommand $command
     * @return AddProductListingCommandHandler
     */
    public function createAddProductListingCommandHandler(AddProductListingCommand $command)
    {
        return new AddProductListingCommandHandler(
            $command,
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @param AddImageCommand $command
     * @return AddImageCommandHandler
     */
    public function createAddImageCommandHandler(AddImageCommand $command)
    {
        return new AddImageCommandHandler(
            $command,
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @return string[]
     */
    public function getRequiredContexts()
    {
        return [WebsiteContextPartBuilder::CODE, LocaleContextPartBuilder::CODE, VersionContextPartBuilder::CODE];
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createContentBlockInProductListingSnippetKeyGenerator()
    {
        return new GenericSnippetKeyGenerator(
            'content_block_in_product_listing',
            $this->getMasterFactory()->getRequiredContexts(),
            [PageMetaInfoSnippetContent::URL_KEY]
        );
    }

    /**
     * @return ProductSearchResultMetaSnippetRenderer
     */
    public function createProductSearchResultMetaSnippetRenderer()
    {
        return new ProductSearchResultMetaSnippetRenderer(
            $this->getMasterFactory()->createProductSearchResultMetaSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductListingBlockRenderer(),
            $this->getMasterFactory()->createContextSource()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductSearchResultMetaSnippetKeyGenerator()
    {
        $usedDataParts = [];

        return new GenericSnippetKeyGenerator(
            ProductSearchResultMetaSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SearchCriteriaBuilder
     */
    public function createSearchCriteriaBuilder()
    {
        return new SearchCriteriaBuilder(
            $this->getMasterFactory()->getFacetFieldTransformationRegistry(),
            $this->getMasterFactory()->createGlobalProductListingCriteria()
        );
    }

    /**
     * @param DomainEventHandler $eventHandlerToDecorate
     * @return ProcessTimeLoggingDomainEventHandlerDecorator
     */
    public function createProcessTimeLoggingDomainEventHandlerDecorator(DomainEventHandler $eventHandlerToDecorate)
    {
        return new ProcessTimeLoggingDomainEventHandlerDecorator(
            $eventHandlerToDecorate,
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @param CommandHandler $commandHandlerToDecorate
     * @return ProcessTimeLoggingCommandHandlerDecorator
     */
    public function createProcessTimeLoggingCommandHandlerDecorator(CommandHandler $commandHandlerToDecorate)
    {
        return new ProcessTimeLoggingCommandHandlerDecorator(
            $commandHandlerToDecorate,
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return CatalogImport
     */
    public function createCatalogImport()
    {
        return new CatalogImport(
            $this->getMasterFactory()->createQueueImportCommands(),
            $this->getMasterFactory()->createProductXmlToProductBuilderLocator(),
            $this->getMasterFactory()->createProductListingBuilder(),
            $this->getMasterFactory()->getEventQueue(),
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return UrlKeyStore
     */
    public function getUrlKeyStore()
    {
        if (null === $this->urlKeyStore) {
            $this->urlKeyStore = $this->getMasterFactory()->createUrlKeyStore();
        }
        return $this->urlKeyStore;
    }

    /**
     * @return TranslatorRegistry
     */
    public function getTranslatorRegistry()
    {
        if (null === $this->translatorRegistry) {
            $this->translatorRegistry = new TranslatorRegistry();

            $this->translatorRegistry->register(
                ProductSearchAutosuggestionSnippetRenderer::CODE,
                $this->getMasterFactory()->getProductSearchAutosuggestionTranslatorFactory()
            );

            $this->translatorRegistry->register(
                ProductListingTemplateSnippetRenderer::CODE,
                $this->getMasterFactory()->getProductListingTranslatorFactory()
            );

            $this->translatorRegistry->register(
                ProductInSearchAutosuggestionSnippetRenderer::CODE,
                $this->getMasterFactory()->getProductInSearchAutosuggestionTranslatorFactory()
            );

            $this->translatorRegistry->register(
                ProductDetailViewSnippetRenderer::CODE,
                $this->getMasterFactory()->getProductDetailsViewTranslatorFactory()
            );
        }

        return $this->translatorRegistry;
    }

    /**
     * @return callable
     */
    public function getProductListingTranslatorFactory()
    {
        return function ($locale) {
            $files = ['common.csv', 'attributes.csv', 'product-listing.csv'];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->createThemeLocator(), $files);
        };
    }

    /**
     * @return callable
     */
    public function getProductDetailsViewTranslatorFactory()
    {
        return function ($locale) {
            $files = ['common.csv', 'attributes.csv', 'product-details.csv'];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->createThemeLocator(), $files);
        };
    }

    /**
     * @return callable
     */
    public function getProductInSearchAutosuggestionTranslatorFactory()
    {
        return function ($locale) {
            $files = [];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->createThemeLocator(), $files);
        };
    }

    /**
     * @return callable
     */
    public function getProductSearchAutosuggestionTranslatorFactory()
    {
        return function ($locale) {
            $files = ['product_search_autosuggestion.csv'];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->createThemeLocator(), $files);
        };
    }

    /**
     * @return EnvironmentConfigReader
     */
    public function createConfigReader()
    {
        return EnvironmentConfigReader::fromGlobalState();
    }

    /**
     * @param CatalogWasImportedDomainEvent $event
     * @return CatalogWasImportedDomainEventHandler
     */
    public function createCatalogWasImportedDomainEventHandler(CatalogWasImportedDomainEvent $event)
    {
        return new CatalogWasImportedDomainEventHandler($event);
    }

    /**
     * @return BaseUrlBuilder
     */
    public function createBaseUrlBuilder()
    {
        return new WebsiteBaseUrlBuilder($this->getMasterFactory()->createConfigReader());
    }

    /**
     * @return FacetFieldTransformationRegistry
     */
    public function getFacetFieldTransformationRegistry()
    {
        if (null === $this->memoizedFacetFieldTransformationRegistry) {
            $this->memoizedFacetFieldTransformationRegistry = $this->getMasterFactory()
                ->createFacetFieldTransformationRegistry();
        }

        return $this->memoizedFacetFieldTransformationRegistry;
    }

    /**
     * @return FilesystemFileStorage
     */
    public function createFilesystemFileStorage()
    {
        return new FilesystemFileStorage($this->getMasterFactory()->getMediaBaseDirectoryConfig());
    }

    /**
     * @return string
     */
    public function getMediaBaseDirectoryConfig()
    {
        /** @var ConfigReader $configReader */
        $configReader = $this->getMasterFactory()->createConfigReader();
        $mediaBasePath = $configReader->get('media_base_path');
        return null === $mediaBasePath ?
            __DIR__ . '/../pub/media' :
            $mediaBasePath;
    }

    /**
     * @return MediaBaseUrlBuilder
     */
    public function createMediaBaseUrlBuilder()
    {
        $mediaBaseUrlPath = 'media/';
        return new MediaDirectoryBaseUrlBuilder(
            $this->getMasterFactory()->createBaseUrlBuilder(),
            $mediaBaseUrlPath
        );
    }

    /**
     * @return AttributeValueCollectorLocator
     */
    public function createAttributeValueCollectorLocator()
    {
        return new AttributeValueCollectorLocator($this->getMasterFactory());
    }

    /**
     * @return DefaultAttributeValueCollector
     */
    public function createDefaultAttributeValueCollector()
    {
        return new DefaultAttributeValueCollector();
    }

    /**
     * @return ConfigurableProductAttributeValueCollector
     */
    public function createConfigurableProductAttributeValueCollector()
    {
        return new ConfigurableProductAttributeValueCollector();
    }

    /**
     * @return QueueImportCommands
     */
    public function createQueueImportCommands()
    {
        return new QueueImportCommands(
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->createProductImportCommandLocator(),
            $this->getMasterFactory()->createProductImageImportCommandLocator(),
            $this->getMasterFactory()->createProductListingImportCommandLocator()
        );
    }

    /**
     * @return ProductImportCommandLocator
     */
    public function createProductImportCommandLocator()
    {
        return new ProductImportCommandLocator($this->getMasterFactory());
    }

    /**
     * @return ProductImageImportCommandLocator
     */
    public function createProductImageImportCommandLocator()
    {
        return new ProductImageImportCommandLocator($this->getMasterFactory());
    }

    /**
     * @return ProductListingImportCommandLocator
     */
    public function createProductListingImportCommandLocator()
    {
        return new ProductListingImportCommandLocator($this->getMasterFactory());
    }

    /**
     * @return ProductListingDescriptionSnippetRenderer
     */
    public function createProductListingDescriptionSnippetRenderer()
    {
        return new ProductListingDescriptionSnippetRenderer(
            $this->getMasterFactory()->createProductListingDescriptionBlockRenderer(),
            $this->getMasterFactory()->createProductListingDescriptionSnippetKeyGenerator(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductListingDescriptionSnippetKeyGenerator()
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            ProductListingDescriptionSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductListingDescriptionBlockRenderer
     */
    public function createProductListingDescriptionBlockRenderer()
    {
        return new ProductListingDescriptionBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createBaseUrlBuilder()
        );
    }

    public function createProductDetailPageMetaDescriptionSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            ProductDetailViewSnippetRenderer::META_DESCRIPTION_CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }
}
