<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\Context\DataVersion\ContextVersion as VersionContextPartBuilder;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEventHandler;
use LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommandHandler;
use LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\SnippetReader;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\Routing\ResourceNotFoundRouter;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Import\GenericSnippetProjector;
use LizardsAndPumpkins\Import\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Import\ImageStorage\MediaBaseUrlBuilder;
use LizardsAndPumpkins\Import\ImageStorage\MediaDirectoryBaseUrlBuilder;
use LizardsAndPumpkins\Import\ImportCatalogCommandHandler;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductImportCommandLocator;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductProjector;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilder;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\QueueImportCommands;
use LizardsAndPumpkins\Import\Product\SimpleProductXmlToProductBuilder;
use LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommandHandler;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\BlockStructure;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerLocator;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerFactory;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerLocator;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\TemplateRendering\ProductDetailViewBlockRenderer;
use LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler;
use LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder;
use LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingProjector;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator;
use LizardsAndPumpkins\ProductSearch\Import\ConfigurableProductAttributeValueCollector;
use LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder;
use LizardsAndPumpkins\Translation\CsvTranslator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Config\EnvironmentConfigReader;
use LizardsAndPumpkins\Util\Factory\Exception\UndefinedFactoryMethodException;

class CommonFactory implements Factory, DomainEventHandlerFactory, CommandHandlerFactory
{
    use FactoryTrait;

    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    /**
     * @var Queue
     */
    private $eventMessageQueue;

    /**
     * @var CommandQueue
     */
    private $commandQueue;

    /**
     * @var Queue
     */
    private $commandMessageQueue;

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
     * @var ThemeLocator
     */
    private $themeLocator;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ContextPartBuilder
     */
    private $localeContextPartBuilder;

    /**
     * @var ContextPartBuilder
     */
    private $countryContextPartBuilder;

    /**
     * @var ContextPartBuilder
     */
    private $websiteContextPartBuilder;

    public function createProductWasUpdatedDomainEventHandler(): DomainEventHandler
    {
        return new ProductWasUpdatedDomainEventHandler(
            $this->getMasterFactory()->createProductProjector()
        );
    }

    public function createTemplateWasUpdatedDomainEventHandler(): DomainEventHandler
    {
        return new TemplateWasUpdatedDomainEventHandler(
            $this->getMasterFactory()->createTemplateProjectorLocator()
        );
    }

    public function createTemplateProjectorLocator(): TemplateProjectorLocator
    {
        $templateProjectorLocator = new TemplateProjectorLocator();
        $templateProjectorLocator->register(
            ProductListingTemplateSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductListingTemplateProjector()
        );
        $templateProjectorLocator->register(
            ProductDetailTemplateSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductDetailTemplateProjector()
        );

        return $templateProjectorLocator;
    }

    public function createProductListingWasAddedDomainEventHandler(): DomainEventHandler
    {
        return new ProductListingWasAddedDomainEventHandler(
            $this->getMasterFactory()->createProductListingProjector()
        );
    }

    public function createProductListingBuilder(): ProductListingBuilder
    {
        return new ProductListingBuilder();
    }

    public function createProductProjector(): ProductProjector
    {
        return new ProductProjector(
            $this->getMasterFactory()->createProductViewLocator(),
            $this->createProductSnippetProjector(),
            $this->getMasterFactory()->createProductSearchDocumentBuilder(),
            $this->createUrlKeyForContextCollector(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    private function createProductSnippetProjector(): Projector
    {
        return new GenericSnippetProjector(
            $this->getMasterFactory()->createDataPoolWriter(),
            ...$this->getMasterFactory()->createProductDetailPageSnippetRendererList()
        );
    }

    public function createUrlKeyForContextCollector(): UrlKeyForContextCollector
    {
        return new UrlKeyForContextCollector(
            $this->getContextSource()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createProductDetailPageSnippetRendererList(): array
    {
        return [
            $this->getMasterFactory()->createProductDetailMetaSnippetRenderer(),
            $this->getMasterFactory()->createPriceSnippetRenderer(),
            $this->getMasterFactory()->createSpecialPriceSnippetRenderer(),
            $this->getMasterFactory()->createProductJsonSnippetRenderer(),
            $this->getMasterFactory()->createConfigurableProductJsonSnippetRenderer(),
        ];
    }

    public function createProductJsonSnippetRenderer(): ProductJsonSnippetRenderer
    {
        return new ProductJsonSnippetRenderer(
            $this->getMasterFactory()->createProductJsonSnippetKeyGenerator()
        );
    }

    public function createProductJsonSnippetKeyGenerator(): SnippetKeyGenerator
    {
        $usedDataParts = [Product::ID, ProductJsonService::SNIPPET_NAME];

        return new GenericSnippetKeyGenerator(
            ProductJsonSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    public function createConfigurableProductJsonSnippetRenderer(): ConfigurableProductJsonSnippetRenderer
    {
        return new ConfigurableProductJsonSnippetRenderer(
            $this->getMasterFactory()->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator()
        );
    }

    public function createConfigurableProductVariationAttributesJsonSnippetKeyGenerator(): SnippetKeyGenerator
    {
        $usedDataParts = ['product_id'];

        return new GenericSnippetKeyGenerator(
            ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    public function createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator(): SnippetKeyGenerator
    {
        $usedDataParts = ['product_id'];

        return new GenericSnippetKeyGenerator(
            ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    public function createProductListingTemplateProjector(): GenericSnippetProjector
    {
        return new GenericSnippetProjector(
            $this->getMasterFactory()->createDataPoolWriter(),
            ...$this->getMasterFactory()->createProductListingTemplateSnippetRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createProductListingTemplateSnippetRendererList(): array
    {
        return [
            $this->getMasterFactory()->createProductListingTemplateSnippetRenderer(),
            $this->getMasterFactory()->createProductSearchResultMetaSnippetRenderer(),
        ];
    }

    public function createProductListingTemplateSnippetRenderer(): ProductListingTemplateSnippetRenderer
    {
        $templateSnippetRenderer = new TemplateSnippetRenderer(
            $this->getMasterFactory()->createProductListingTemplateSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductListingBlockRenderer(),
            $this->getMasterFactory()->getContextSource()
        );

        return new ProductListingTemplateSnippetRenderer($templateSnippetRenderer);
    }

    public function createProductListingTemplateSnippetKeyGenerator(): SnippetKeyGenerator
    {
        return new GenericSnippetKeyGenerator(
            ProductListingTemplateSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts = []
        );
    }

    public function createProductListingBlockRenderer(): ProductListingBlockRenderer
    {
        return new ProductListingBlockRenderer(
            $this->getMasterFactory()->getThemeLocator(),
            $this->getMasterFactory()->createBlockStructure(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createBaseUrlBuilder(),
            $this->getMasterFactory()->createAssetsBaseUrlBuilder()
        );
    }

    public function createProductListingProjector(): ProductListingProjector
    {
        return new ProductListingProjector(
            $this->createProductListingSnippetProjector(),
            $this->getMasterFactory()->createUrlKeyForContextCollector(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    private function createProductListingSnippetProjector(): Projector
    {
        return new GenericSnippetProjector(
            $this->getMasterFactory()->createDataPoolWriter(),
            ...$this->getMasterFactory()->createProductListingSnippetRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createProductListingSnippetRendererList(): array
    {
        return [
            $this->getMasterFactory()->createProductListingMetaSnippetRenderer(),
        ];
    }

    public function createProductListingMetaSnippetRenderer(): ProductListingMetaSnippetRenderer
    {
        return new ProductListingMetaSnippetRenderer(
            $this->getMasterFactory()->createProductListingBlockRenderer(),
            $this->getMasterFactory()->createProductListingSnippetKeyGenerator(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    public function createProductListingSnippetKeyGenerator(): SnippetKeyGenerator
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            'meta',
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    public function createProductDetailMetaSnippetRenderer(): ProductDetailMetaSnippetRenderer
    {
        return new ProductDetailMetaSnippetRenderer(
            $this->getMasterFactory()->createProductDetailViewBlockRenderer(),
            $this->getMasterFactory()->createProductDetailPageMetaSnippetKeyGenerator()
        );
    }

    public function createProductDetailTemplateProjector(): GenericSnippetProjector
    {
        return new GenericSnippetProjector(
            $this->getMasterFactory()->createDataPoolWriter(),
            ...$this->getMasterFactory()->createProductDetailTemplateSnippetRendererList()
        );
    }

    public function createProductDetailTemplateSnippetRendererList()
    {
        return [
            $this->getMasterFactory()->createProductDetailTemplateSnippetRenderer(),
        ];
    }

    public function createProductDetailTemplateSnippetRenderer(): ProductDetailTemplateSnippetRenderer
    {
        $templateSnippetRenderer = new TemplateSnippetRenderer(
            $this->getMasterFactory()->createProductDetailTemplateSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductDetailViewBlockRenderer(),
            $this->getMasterFactory()->getContextSource()
        );

        return new ProductDetailTemplateSnippetRenderer($templateSnippetRenderer);
    }

    public function createProductDetailTemplateSnippetKeyGenerator(): SnippetKeyGenerator
    {
        return new GenericSnippetKeyGenerator(
            ProductDetailTemplateSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts = []
        );
    }

    public function createProductDetailViewBlockRenderer(): ProductDetailViewBlockRenderer
    {
        return new ProductDetailViewBlockRenderer(
            $this->getMasterFactory()->getThemeLocator(),
            $this->getMasterFactory()->createBlockStructure(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createBaseUrlBuilder(),
            $this->getMasterFactory()->createAssetsBaseUrlBuilder()
        );
    }

    public function createProductDetailPageMetaSnippetKeyGenerator(): SnippetKeyGenerator
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            'meta',
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    public function createPriceSnippetRenderer(): PriceSnippetRenderer
    {
        $productRegularPriceAttributeCode = AttributeCode::fromString('price');

        return new PriceSnippetRenderer(
            $this->getMasterFactory()->createTaxableCountries(),
            $this->getMasterFactory()->createTaxServiceLocator(),
            $this->getMasterFactory()->createPriceSnippetKeyGenerator(),
            $this->createContextBuilder(),
            $productRegularPriceAttributeCode
        );
    }

    public function createSpecialPriceSnippetRenderer(): PriceSnippetRenderer
    {
        $productSpecialPriceAttributeCode = AttributeCode::fromString('special_price');

        return new PriceSnippetRenderer(
            $this->getMasterFactory()->createTaxableCountries(),
            $this->getMasterFactory()->createTaxServiceLocator(),
            $this->getMasterFactory()->createSpecialPriceSnippetKeyGenerator(),
            $this->createContextBuilder(),
            $productSpecialPriceAttributeCode
        );
    }

    public function createPriceSnippetKeyGenerator(): SnippetKeyGenerator
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            PriceSnippetRenderer::PRICE,
            $this->getPriceSnippetKeyContextPartCodes(),
            $usedDataParts
        );
    }

    public function createSpecialPriceSnippetKeyGenerator(): SnippetKeyGenerator
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
    private function getPriceSnippetKeyContextPartCodes(): array
    {
        return [Website::CONTEXT_CODE, Country::CONTEXT_CODE];
    }

    public function createContentBlockSnippetKeyGenerator(string $snippetCode): SnippetKeyGenerator
    {
        $usedDataParts = [];

        return new GenericSnippetKeyGenerator(
            $snippetCode,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    public function createProductListingContentBlockSnippetKeyGenerator(string $snippetCode): SnippetKeyGenerator
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            $snippetCode,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    public function createContentBlockSnippetKeyGeneratorLocatorStrategy(): SnippetKeyGeneratorLocator
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

    public function createBlockStructure(): BlockStructure
    {
        return new BlockStructure();
    }

    public function createProductXmlToProductBuilderLocator(): ProductXmlToProductBuilderLocator
    {
        $productXmlToProductTypeBuilders = $this->getMasterFactory()->createProductXmlToProductTypeBuilders();
        return new ProductXmlToProductBuilderLocator(...$productXmlToProductTypeBuilders);
    }

    /**
     * @return ProductXmlToProductBuilder[]
     */
    public function createProductXmlToProductTypeBuilders(): array
    {
        return [
            $this->getMasterFactory()->createSimpleProductXmlToProductBuilder(),
            $this->getMasterFactory()->createConfigurableProductXmlToProductBuilder(),
        ];
    }

    public function createSimpleProductXmlToProductBuilder(): SimpleProductXmlToProductBuilder
    {
        return new SimpleProductXmlToProductBuilder();
    }

    public function createConfigurableProductXmlToProductBuilder(): ConfigurableProductXmlToProductBuilder
    {
        $productTypeBuilderFactoryProxy = $this->getMasterFactory()
            ->createProductXmlToProductBuilderLocatorProxyFactoryMethod();
        return new ConfigurableProductXmlToProductBuilder($productTypeBuilderFactoryProxy);
    }

    public function createProductXmlToProductBuilderLocatorProxyFactoryMethod(): \Closure
    {
        return function () {
            return $this->createProductXmlToProductBuilderLocator();
        };
    }

    public function getThemeLocator(): ThemeLocator
    {
        if (null === $this->themeLocator) {
            $this->themeLocator = $this->callExternalCreateMethod('ThemeLocator');
        }

        return $this->themeLocator;
    }

    public function getContextSource(): ContextSource
    {
        if (null === $this->contextSource) {
            $this->contextSource = $this->callExternalCreateMethod('ContextSource');
        }

        return $this->contextSource;
    }

    public function createContextBuilder(): ContextBuilder
    {
        return new SelfContainedContextBuilder(
            $this->getMasterFactory()->createVersionContextPartBuilder(),
            $this->getMasterFactory()->getWebsiteContextPartBuilder(),
            $this->getMasterFactory()->getCountryContextPartBuilder(),
            $this->getMasterFactory()->getLocaleContextPartBuilder()
        );
    }

    public function createVersionContextPartBuilder(): VersionContextPartBuilder
    {
        $dataVersion = $this->getCurrentDataVersion();
        return new VersionContextPartBuilder(DataVersion::fromVersionString($dataVersion));
    }

    public function getWebsiteContextPartBuilder(): ContextPartBuilder
    {
        if (null === $this->websiteContextPartBuilder) {
            $this->websiteContextPartBuilder = $this->callExternalCreateMethod('WebsiteContextPartBuilder');
        }

        return $this->websiteContextPartBuilder;
    }

    public function getLocaleContextPartBuilder(): ContextPartBuilder
    {
        if (null === $this->localeContextPartBuilder) {
            $this->localeContextPartBuilder = $this->callExternalCreateMethod('LocaleContextPartBuilder');
        }

        return $this->localeContextPartBuilder;
    }

    public function getCountryContextPartBuilder(): ContextPartBuilder
    {
        if (null === $this->countryContextPartBuilder) {
            $this->countryContextPartBuilder = $this->callExternalCreateMethod('CountryContextPartBuilder');
        }

        return $this->countryContextPartBuilder;
    }

    public function getCurrentDataVersion(): string
    {
        if (null === $this->currentDataVersion) {
            /** @var DataPoolReader $dataPoolReader */
            $dataPoolReader = $this->getMasterFactory()->createDataPoolReader();
            $this->currentDataVersion = $dataPoolReader->getCurrentDataVersion();
        }

        return $this->currentDataVersion;
    }

    public function createDomainEventHandlerLocator(): DomainEventHandlerLocator
    {
        return new DomainEventHandlerLocator($this->getMasterFactory());
    }

    public function createDataPoolWriter(): DataPoolWriter
    {
        return new DataPoolWriter(
            $this->getMasterFactory()->getKeyValueStore(),
            $this->getMasterFactory()->getSearchEngine(),
            $this->getMasterFactory()->getUrlKeyStore()
        );
    }

    public function getKeyValueStore(): KeyValueStore
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->callExternalCreateMethod('KeyValueStore');
        }

        return $this->keyValueStore;
    }

    public function createDomainEventConsumer(): DomainEventConsumer
    {
        return new DomainEventConsumer(
            $this->getMasterFactory()->getEventMessageQueue(),
            $this->getMasterFactory()->createDomainEventHandlerLocator(),
            $this->getLogger()
        );
    }

    public function getEventQueue(): DomainEventQueue
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->callExternalCreateMethod('EventQueue');
        }

        return $this->eventQueue;
    }

    public function getEventMessageQueue(): Queue
    {
        if (null === $this->eventMessageQueue) {
            $this->eventMessageQueue = $this->callExternalCreateMethod('EventMessageQueue');
        }
        return $this->eventMessageQueue;
    }

    public function createDataPoolReader(): DataPoolReader
    {
        return new DataPoolReader(
            $this->getMasterFactory()->getKeyValueStore(),
            $this->getMasterFactory()->getSearchEngine(),
            $this->getMasterFactory()->getUrlKeyStore()
        );
    }

    public function createSnippetReader(): SnippetReader
    {
        return new SnippetReader(
            $this->getMasterFactory()->getKeyValueStore(),
            ...$this->getMasterFactory()->getRequiredContextParts()
        );
    }

    public function getLogger(): Logger
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
    private function callExternalCreateMethod(string $targetObjectName)
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

    public function createResourceNotFoundRouter(): ResourceNotFoundRouter
    {
        return new ResourceNotFoundRouter();
    }

    public function createHttpRouterChain(): HttpRouterChain
    {
        return new HttpRouterChain();
    }

    public function createProductSearchDocumentBuilder(): ProductSearchDocumentBuilder
    {
        $indexAttributeCodes = array_unique(array_merge(
            $this->getMasterFactory()->getSearchableAttributeCodes(),
            $this->getMasterFactory()->getFacetFilterRequestFieldCodesForSearchDocuments(),
            $this->getMasterFactory()->getSortableAttributeCodes()
        ));

        return new ProductSearchDocumentBuilder(
            $indexAttributeCodes,
            $this->getMasterFactory()->createAttributeValueCollectorLocator(),
            $this->getMasterFactory()->createTaxableCountries(),
            $this->getMasterFactory()->createTaxServiceLocator()
        );
    }

    public function getSearchEngine(): SearchEngine
    {
        if (null === $this->searchEngine) {
            $this->searchEngine = $this->callExternalCreateMethod('SearchEngine');
        }

        return $this->searchEngine;
    }

    public function createImageWasAddedDomainEventHandler(): DomainEventHandler
    {
        return new ImageWasAddedDomainEventHandler(
            $this->getMasterFactory()->createImageProcessorCollection()
        );
    }

    public function getImageProcessorCollection(): ImageProcessorCollection
    {
        if (null === $this->imageProcessorCollection) {
            $this->imageProcessorCollection = $this->callExternalCreateMethod('ImageProcessorCollection');
        }

        return $this->imageProcessorCollection;
    }

    public function createCommandConsumer(): CommandConsumer
    {
        return new CommandConsumer(
            $this->getMasterFactory()->getCommandMessageQueue(),
            $this->getMasterFactory()->createCommandHandlerLocator(),
            $this->getLogger()
        );
    }

    public function getCommandQueue(): CommandQueue
    {
        if (null === $this->commandQueue) {
            $this->commandQueue = $this->callExternalCreateMethod('CommandQueue');
        }

        return $this->commandQueue;
    }

    public function getCommandMessageQueue(): Queue
    {
        if (null === $this->commandMessageQueue) {
            $this->commandMessageQueue = $this->callExternalCreateMethod('CommandMessageQueue');
        }
        return $this->commandMessageQueue;
    }

    public function createCommandHandlerLocator(): CommandHandlerLocator
    {
        return new CommandHandlerLocator($this->getMasterFactory());
    }

    public function createUpdateContentBlockCommandHandler(): CommandHandler
    {
        return new UpdateContentBlockCommandHandler($this->getMasterFactory()->getEventQueue());
    }

    public function createUpdateTemplateCommandHandler(): CommandHandler
    {
        return new UpdateTemplateCommandHandler($this->getMasterFactory()->getEventQueue());
    }

    public function createContentBlockWasUpdatedDomainEventHandler(): DomainEventHandler
    {
        return new ContentBlockWasUpdatedDomainEventHandler(
            $this->getMasterFactory()->createContentBlockProjector()
        );
    }

    public function createContentBlockProjector(): ContentBlockProjector
    {
        return new ContentBlockProjector(
            $this->createContentBlockSnippetProjector()
        );
    }

    private function createContentBlockSnippetProjector(): Projector
    {
        return new GenericSnippetProjector(
            $this->getMasterFactory()->createDataPoolWriter(),
            ...$this->getMasterFactory()->createContentBlockSnippetRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createContentBlockSnippetRendererList(): array
    {
        return [
            $this->getMasterFactory()->createContentBlockSnippetRenderer(),
        ];
    }

    public function createContentBlockSnippetRenderer(): ContentBlockSnippetRenderer
    {
        return new ContentBlockSnippetRenderer(
            $this->getMasterFactory()->createContentBlockSnippetKeyGeneratorLocatorStrategy()
        );
    }

    public function createUpdateProductCommandHandler(): CommandHandler
    {
        return new UpdateProductCommandHandler(
            $this->getMasterFactory()->getEventQueue()
        );
    }

    public function createAddProductListingCommandHandler(): CommandHandler
    {
        return new AddProductListingCommandHandler(
            $this->getMasterFactory()->getEventQueue()
        );
    }

    public function createAddImageCommandHandler(): CommandHandler
    {
        return new AddImageCommandHandler(
            $this->getMasterFactory()->getEventQueue()
        );
    }

    public function createShutdownWorkerCommandHandler(): CommandHandler
    {
        return new ShutdownWorkerDirectiveHandler(
            Queue\EnqueuesMessageEnvelope::fromCommandQueue($this->getMasterFactory()->getCommandQueue()),
            $this->getMasterFactory()->getLogger()
        );
    }

    public function createImportCatalogCommandHandler(): CommandHandler
    {
        return new ImportCatalogCommandHandler(
            $this->getMasterFactory()->getEventQueue()
        );
    }

    public function createSetCurrentDataVersionCommandHandler(): CommandHandler
    {
        return new SetCurrentDataVersionCommandHandler(
            $this->getMasterFactory()->getEventQueue(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    /**
     * @return string[]
     */
    public function getRequiredContextParts(): array
    {
        return [Website::CONTEXT_CODE, Locale::CONTEXT_CODE, DataVersion::CONTEXT_CODE];
    }

    public function createContentBlockInProductListingSnippetKeyGenerator(): SnippetKeyGenerator
    {
        return new GenericSnippetKeyGenerator(
            'content_block_in_product_listing',
            $this->getMasterFactory()->getRequiredContextParts(),
            [PageMetaInfoSnippetContent::URL_KEY]
        );
    }

    public function createProductSearchResultMetaSnippetRenderer(): ProductSearchResultMetaSnippetRenderer
    {
        return new ProductSearchResultMetaSnippetRenderer(
            $this->getMasterFactory()->createProductSearchResultMetaSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductListingBlockRenderer(),
            $this->getMasterFactory()->getContextSource()
        );
    }

    public function createProductSearchResultMetaSnippetKeyGenerator(): SnippetKeyGenerator
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            'meta',
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    public function createCatalogImport(): CatalogImport
    {
        return new CatalogImport(
            $this->getMasterFactory()->createQueueImportCommands(),
            $this->getMasterFactory()->createProductXmlToProductBuilderLocator(),
            $this->getMasterFactory()->createProductListingBuilder(),
            $this->getMasterFactory()->getEventQueue(),
            $this->getMasterFactory()->getContextSource(),
            $this->getMasterFactory()->getLogger()
        );
    }

    public function getUrlKeyStore(): UrlKeyStore
    {
        if (null === $this->urlKeyStore) {
            $this->urlKeyStore = $this->getMasterFactory()->createUrlKeyStore();
        }
        return $this->urlKeyStore;
    }

    public function getTranslatorRegistry(): TranslatorRegistry
    {
        if (null === $this->translatorRegistry) {
            $this->translatorRegistry = new TranslatorRegistry();

            $this->translatorRegistry->register(
                ProductListingTemplateSnippetRenderer::CODE,
                $this->getMasterFactory()->getProductListingTranslatorFactory()
            );

            $this->translatorRegistry->register(
                ProductDetailTemplateSnippetRenderer::CODE,
                $this->getMasterFactory()->getProductDetailsViewTranslatorFactory()
            );
        }

        return $this->translatorRegistry;
    }

    public function getProductListingTranslatorFactory(): callable
    {
        return function ($locale) {
            $files = ['common.csv', 'attributes.csv', 'product-listing.csv'];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->getThemeLocator(), $files);
        };
    }

    public function getProductDetailsViewTranslatorFactory(): callable
    {
        return function ($locale) {
            $files = ['common.csv', 'attributes.csv', 'product-details.csv'];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->getThemeLocator(), $files);
        };
    }

    public function createConfigReader(): EnvironmentConfigReader
    {
        return EnvironmentConfigReader::fromGlobalState();
    }

    public function createCatalogWasImportedDomainEventHandler(): DomainEventHandler
    {
        return new CatalogWasImportedDomainEventHandler();
    }

    public function createShutdownWorkerDomainEventHandler(): DomainEventHandler
    {
        return new ShutdownWorkerDirectiveHandler(
            Queue\EnqueuesMessageEnvelope::fromDomainEventQueue($this->getMasterFactory()->getEventQueue()),
            $this->getMasterFactory()->getLogger()
        );
    }

    public function createCatalogImportWasTriggeredDomainEventHandler(): DomainEventHandler
    {
        return new CatalogImportWasTriggeredDomainEventHandler(
            $this->getMasterFactory()->createCatalogImport()
        );
    }

    public function createCurrentDataVersionWasSetDomainEventHandler(): DomainEventHandler
    {
        return new CurrentDataVersionWasSetDomainEventHandler();
    }

    public function createBaseUrlBuilder(): BaseUrlBuilder
    {
        return new WebsiteBaseUrlBuilder($this->getMasterFactory()->createConfigReader());
    }

    public function createAssetsBaseUrlBuilder(): BaseUrlBuilder
    {
        return new WebsiteBaseUrlBuilder($this->getMasterFactory()->createConfigReader());
    }

    public function getFacetFieldTransformationRegistry(): FacetFieldTransformationRegistry
    {
        if (null === $this->memoizedFacetFieldTransformationRegistry) {
            $this->memoizedFacetFieldTransformationRegistry = $this->getMasterFactory()
                ->createFacetFieldTransformationRegistry();
        }

        return $this->memoizedFacetFieldTransformationRegistry;
    }

    public function createFilesystemFileStorage(): FilesystemFileStorage
    {
        return new FilesystemFileStorage($this->getMasterFactory()->getMediaBaseDirectoryConfig());
    }

    public function getMediaBaseDirectoryConfig(): string
    {
        /** @var ConfigReader $configReader */
        $configReader = $this->getMasterFactory()->createConfigReader();

        return $configReader->has('media_base_path') ?
            $configReader->get('media_base_path') :
            __DIR__ . '/../pub/media';
    }

    public function createMediaBaseUrlBuilder(): MediaBaseUrlBuilder
    {
        return new MediaDirectoryBaseUrlBuilder(
            $this->getMasterFactory()->createBaseUrlBuilder(),
            $mediaBaseUrlPath = 'media/'
        );
    }

    public function createAttributeValueCollectorLocator(): AttributeValueCollectorLocator
    {
        return new AttributeValueCollectorLocator($this->getMasterFactory());
    }

    public function createDefaultAttributeValueCollector(): DefaultAttributeValueCollector
    {
        return new DefaultAttributeValueCollector();
    }

    public function createConfigurableProductAttributeValueCollector(): ConfigurableProductAttributeValueCollector
    {
        return new ConfigurableProductAttributeValueCollector();
    }

    public function createQueueImportCommands(): QueueImportCommands
    {
        return new QueueImportCommands(
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->createProductImportCommandLocator(),
            $this->getMasterFactory()->createProductImageImportCommandLocator(),
            $this->getMasterFactory()->createProductListingImportCommandLocator()
        );
    }

    public function createProductImportCommandLocator(): ProductImportCommandLocator
    {
        return new ProductImportCommandLocator($this->getMasterFactory());
    }

    public function createProductImageImportCommandLocator(): ProductImageImportCommandLocator
    {
        return new ProductImageImportCommandLocator($this->getMasterFactory());
    }

    public function createProductListingImportCommandLocator(): ProductListingImportCommandLocator
    {
        return new ProductListingImportCommandLocator($this->getMasterFactory());
    }

    public function createProductJsonService(): ProductJsonService
    {
        return new ProductJsonService(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createPriceSnippetKeyGenerator(),
            $this->getMasterFactory()->createSpecialPriceSnippetKeyGenerator(),
            $this->getMasterFactory()->createEnrichProductJsonWithPrices()
        );
    }

    public function createEnrichProductJsonWithPrices(): EnrichProductJsonWithPrices
    {
        return new EnrichProductJsonWithPrices();
    }
}
