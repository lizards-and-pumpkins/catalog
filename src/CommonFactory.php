<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Content\ContentBlockProjector;
use LizardsAndPumpkins\Content\ContentBlockSnippetRenderer;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\LocaleContextDecorator;
use LizardsAndPumpkins\Context\WebsiteContextDecorator;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
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
use LizardsAndPumpkins\Product\ProductBackOrderAvailabilitySnippetRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewBlockRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionBlockRenderer;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\ProductsPerPageForContextListBuilder;
use LizardsAndPumpkins\Product\ProductListingTemplateProjector;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionBlockRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionTemplateProjector;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductListingBlockRenderer;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetProjector;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetProjector;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\Product\ProductProjector;
use LizardsAndPumpkins\Product\ProductListingCriteriaBuilder;
use LizardsAndPumpkins\Product\ProductSearchDocumentBuilder;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\SimpleProductXmlToProductBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToProductBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilder;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductStockQuantityProjector;
use LizardsAndPumpkins\Product\ProductStockQuantitySnippetRenderer;
use LizardsAndPumpkins\Product\ProductStockQuantitySourceBuilder;
use LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommand;
use LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\AddProductListingCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductStockQuantityCommand;
use LizardsAndPumpkins\Product\UpdateProductStockQuantityCommandHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport;
use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
use LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Renderer\BlockStructure;
use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\CsvTranslator;
use LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry;

class CommonFactory implements Factory, DomainEventFactory, CommandFactory
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
            ProductListingPageSnippetRenderer::CODE,
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
            $this->getMasterFactory()->createProductListingCriteriaSnippetProjector()
        );
    }

    /**
     * @return ProductsPerPageForContextListBuilder
     */
    public function createProductsPerPageForContextListBuilder()
    {
        return new ProductsPerPageForContextListBuilder($this->getMasterFactory()->createContextBuilder());
    }

    /**
     * @return ProductListingCriteriaBuilder
     */
    public function createProductListingCriteriaBuilder()
    {
        return new ProductListingCriteriaBuilder();
    }

    /**
     * @return ProductProjector
     */
    public function createProductProjector()
    {
        return new ProductProjector(
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
            $this->createProductSnippetRendererList(),
            $this->getMasterFactory()->createSnippetList()
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
            $this->getMasterFactory()->createProductJsonSnippetRenderer(),
            $this->getMasterFactory()->createConfigurableProductJsonSnippetRenderer(),
            $this->getMasterFactory()->createProductBackOrderAvailabilitySnippetRenderer()
        ];
    }

    /**
     * @return ProductJsonSnippetRenderer
     */
    public function createProductJsonSnippetRenderer()
    {
        return new ProductJsonSnippetRenderer(
            $this->getMasterFactory()->createProductJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createInternalToPublicProductJsonData()
        );
    }

    /**
     * @return GenericSnippetKeyGenerator
     */
    public function createProductJsonSnippetKeyGenerator()
    {
        $usedDataParts = ['product_id'];

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
            $this->getMasterFactory()->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createInternalToPublicProductJsonData()
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
     * @return InternalToPublicProductJsonData
     */
    public function createInternalToPublicProductJsonData()
    {
        return new InternalToPublicProductJsonData();
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
            $this->createProductSearchAutosuggestionRendererList(),
            $this->getMasterFactory()->createSnippetList()
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
            $this->getMasterFactory()->createSnippetList(),
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
            $this->getMasterFactory()->createSnippetList(),
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
            $this->getMasterFactory()->getTranslatorRegistry()
        );
    }

    /**
     * @return ProductListingTemplateProjector
     */
    public function createProductListingTemplateProjector()
    {
        return new ProductListingTemplateProjector(
            $this->createProductListingTemplateRendererCollection(),
            $this->getMasterFactory()->createDataPoolWriter(),
            $this->getMasterFactory()->createProductsPerPageForContextListBuilder()
        );
    }

    /**
     * @return SnippetRendererCollection
     */
    private function createProductListingTemplateRendererCollection()
    {
        return new SnippetRendererCollection(
            $this->createProductListingRendererList(),
            $this->getMasterFactory()->createSnippetList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    private function createProductListingRendererList()
    {
        return [
            $this->getMasterFactory()->createProductSearchResultMetaSnippetRenderer(),
        ];
    }

    /**
     * @return ProductListingPageSnippetRenderer
     */
    public function createProductListingPageSnippetRenderer()
    {
        return new ProductListingPageSnippetRenderer(
            $this->getMasterFactory()->createProductListingSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductListingBlockRenderer()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductListingSnippetKeyGenerator()
    {
        $usedDataParts = [];

        return new GenericSnippetKeyGenerator(
            ProductListingPageSnippetRenderer::CODE,
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
            $this->getMasterFactory()->getTranslatorRegistry()
        );
    }

    /**
     * @return ProductListingCriteriaSnippetProjector
     */
    public function createProductListingCriteriaSnippetProjector()
    {
        return new ProductListingCriteriaSnippetProjector(
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
            $this->createProductListingSnippetRendererList(),
            $this->getMasterFactory()->createSnippetList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createProductListingSnippetRendererList()
    {
        return [
            $this->getMasterFactory()->createProductListingCriteriaSnippetRenderer()
        ];
    }

    /**
     * @return ProductListingCriteriaSnippetRenderer
     */
    public function createProductListingCriteriaSnippetRenderer()
    {
        return new ProductListingCriteriaSnippetRenderer(
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createProductListingBlockRenderer(),
            $this->getMasterFactory()->createProductListingCriteriaSnippetKeyGenerator(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    /**
     * @return SnippetList
     */
    public function createSnippetList()
    {
        return new SnippetList();
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductListingCriteriaSnippetKeyGenerator()
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            ProductListingCriteriaSnippetRenderer::CODE,
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
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createProductDetailViewBlockRenderer(),
            $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductDetailPageMetaSnippetKeyGenerator()
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
            $this->getMasterFactory()->getTranslatorRegistry()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductDetailViewSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            'product_detail_view',
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
            $this->getMasterFactory()->createProductInListingSnippetKeyGenerator(),
            $this->getMasterFactory()->createInternalToPublicProductJsonData()
        );
    }

    /**
     * @return ProductInSearchAutosuggestionSnippetRenderer
     */
    public function createProductInSearchAutosuggestionSnippetRenderer()
    {
        return new ProductInSearchAutosuggestionSnippetRenderer(
            $this->getMasterFactory()->createSnippetList(),
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
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createPriceSnippetKeyGenerator(),
            $productRegularPriceAttributeCode
        );
    }

    /**
     * @return ProductBackOrderAvailabilitySnippetRenderer
     */
    public function createProductBackOrderAvailabilitySnippetRenderer()
    {
        $productBackOrderAvailabilityAttributeCode = 'backorders';

        return new ProductBackOrderAvailabilitySnippetRenderer(
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createProductBackOrderAvailabilitySnippetKeyGenerator(),
            $productBackOrderAvailabilityAttributeCode
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
            $this->getMasterFactory()->getTranslatorRegistry()
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
            $this->getMasterFactory()->getRegularPriceSnippetKey(),
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductBackOrderAvailabilitySnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            $this->getMasterFactory()->getProductBackOrderAvailabilitySnippetKey(),
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createContentBlockSnippetKeyGenerator()
    {
        $usedDataParts = ['content_block_id'];

        return new GenericSnippetKeyGenerator(
            $this->getMasterFactory()->getContentBlockSnippetKey(),
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
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
            $this->getMasterFactory()->createConfigurableProductXmlToProductBuilder()
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
        return new SampleContextSource($this->getMasterFactory()->createContextBuilder());
    }

    /**
     * @return ContextBuilder
     */
    public function createContextBuilder()
    {
        $version = $this->getCurrentDataVersion();

        return $this->createContextBuilderWithVersion(DataVersion::fromVersionString($version));
    }

    /**
     * @param DataVersion $version
     * @return ContextBuilder
     */
    public function createContextBuilderWithVersion(DataVersion $version)
    {
        $contextBuilder = new ContextBuilder($version);
        $contextBuilder->registerContextDecorator('website', WebsiteContextDecorator::class);
        $contextBuilder->registerContextDecorator('locale', LocaleContextDecorator::class);
        return $contextBuilder;
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
        $indexAttributeCodes = array_merge(
            $this->getMasterFactory()->getSearchableAttributeCodes(),
            array_keys($this->getMasterFactory()->getProductListingFilterNavigationConfig()),
            array_keys($this->getMasterFactory()->getProductSearchResultsFilterNavigationConfig())
        );

        return new ProductSearchDocumentBuilder($indexAttributeCodes);
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
     * @return string
     */
    public function getRegularPriceSnippetKey()
    {
        return 'price';
    }

    /**
     * @return string
     */
    public function getProductBackOrderAvailabilitySnippetKey()
    {
        return 'backorders';
    }

    /**
     * @return string
     */
    public function getContentBlockSnippetKey()
    {
        return 'content_block';
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
     * @param UpdateProductStockQuantityCommand $command
     * @return UpdateProductStockQuantityCommandHandler
     */
    public function createUpdateProductStockQuantityCommandHandler(UpdateProductStockQuantityCommand $command)
    {
        return new UpdateProductStockQuantityCommandHandler(
            $command,
            $this->getMasterFactory()->getEventQueue(),
            $this->getMasterFactory()->createProductStockQuantitySourceBuilder()
        );
    }

    /**
     * @param UpdateMultipleProductStockQuantityCommand $command
     * @return UpdateMultipleProductStockQuantityCommandHandler
     */
    public function createUpdateMultipleProductStockQuantityCommandHandler(
        UpdateMultipleProductStockQuantityCommand $command
    ) {
        return new UpdateMultipleProductStockQuantityCommandHandler(
            $command,
            $this->getMasterFactory()->getCommandQueue()
        );
    }

    /**
     * @return ProductStockQuantitySourceBuilder
     */
    public function createProductStockQuantitySourceBuilder()
    {
        return new ProductStockQuantitySourceBuilder();
    }

    /**
     * @return ProductStockQuantityProjector
     */
    public function createProductStockQuantityProjector()
    {
        return new ProductStockQuantityProjector(
            $this->getMasterFactory()->createDataPoolWriter(),
            $this->getMasterFactory()->createProductStockQuantitySnippetRendererCollection()
        );
    }

    /**
     * @return SnippetRendererCollection
     */
    public function createProductStockQuantitySnippetRendererCollection()
    {
        return new SnippetRendererCollection(
            $this->getMasterFactory()->createProductStockQuantitySnippetRendererList(),
            $this->getMasterFactory()->createSnippetList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createProductStockQuantitySnippetRendererList()
    {
        return [$this->getMasterFactory()->createProductStockQuantitySnippetRenderer()];
    }

    /**
     * @return ProductStockQuantitySnippetRenderer
     */
    public function createProductStockQuantitySnippetRenderer()
    {
        return new ProductStockQuantitySnippetRenderer(
            $this->getMasterFactory()->createProductStockQuantityRendererSnippetKeyGenerator(),
            $this->getMasterFactory()->createContextBuilder(),
            $this->getMasterFactory()->createSnippetList()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductStockQuantityRendererSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            ProductStockQuantitySnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
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
        return new CommandHandlerLocator($this);
    }

    /**
     * @param ProductStockQuantityWasUpdatedDomainEvent $event
     * @return ProductStockQuantityWasUpdatedDomainEventHandler
     */
    public function createProductStockQuantityWasUpdatedDomainEventHandler(
        ProductStockQuantityWasUpdatedDomainEvent $event
    ) {
        return new ProductStockQuantityWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->createProductStockQuantityProjector()
        );
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
            $this->getMasterFactory()->createContextSource(),
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
            $this->getMasterFactory()->createContentBlockSnippetRendererList(),
            $this->getMasterFactory()->createSnippetList()
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
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createContentBlockSnippetKeyGenerator(),
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
        return ['website', 'locale', 'version'];
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
            $this->getMasterFactory()->createSnippetList(),
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
        return new SearchCriteriaBuilder;
    }

    /**
     * @param DomainEventHandler $eventHandlerToDecorate
     * @return ProcessTimeLoggingDomainEventHandlerDecorator
     */
    public function createProcessTimeLoggingDomainEventDecorator(DomainEventHandler $eventHandlerToDecorate)
    {
        return new ProcessTimeLoggingDomainEventHandlerDecorator(
            $eventHandlerToDecorate,
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return CatalogImport
     */
    public function createCatalogImport()
    {
        return new CatalogImport(
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->createProductXmlToProductBuilderLocator(),
            $this->getMasterFactory()->createProductListingCriteriaBuilder(),
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
            $this->translatorRegistry = new TranslatorRegistry(
                $this->getMasterFactory()->getTranslatorFactory()
            );
        }

        return $this->translatorRegistry;
    }

    /**
     * @return callable
     */
    public function getTranslatorFactory()
    {
        return function ($locale) {
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->createThemeLocator());
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
        $projector = $this->createProductListingPageSnippetProjector();
        return new CatalogWasImportedDomainEventHandler($event, $projector);
    }

    /**
     * @return ProductListingPageSnippetProjector
     */
    public function createProductListingPageSnippetProjector()
    {
        return new ProductListingPageSnippetProjector(
            $this->getMasterFactory()->createProductListingPageSnippetRenderer(),
            $this->getMasterFactory()->createDataPoolWriter(),
            $this->getMasterFactory()->createContextSource()
        );
    }
}
