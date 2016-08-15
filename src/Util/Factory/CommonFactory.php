<?php

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\DataVersion\ContextVersion as VersionContextPartBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Product\ProductAvailability;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventBuilder;
use LizardsAndPumpkins\Import\Product\UpdateProductCommandBuilder;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\Import\SnippetRendererCollection;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerLocator;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerFactory;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerLocator;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\ProductListingCanonicalTagSnippetRenderer;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Config\EnvironmentConfigReader;
use LizardsAndPumpkins\Util\Factory\Exception\UndefinedFactoryMethodException;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\Routing\ResourceNotFoundRouter;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Import\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\TemplateRendering\ProductDetailViewBlockRenderer;
use LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\TemplateRendering\ProductInSearchAutosuggestionBlockRenderer;
use LizardsAndPumpkins\ProductSearch\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingDescriptionBlockRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateProjector;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ConfigurableProductAttributeValueCollector;
use LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector;
use LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator;
use LizardsAndPumpkins\ProductSearch\Import\TemplateRendering\ProductSearchAutosuggestionBlockRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionTemplateProjector;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Import\Product\ProductImportCommandLocator;
use LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductProjector;
use LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\QueueImportCommands;
use LizardsAndPumpkins\Import\Product\SimpleProductXmlToProductBuilder;
use LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilder;
use LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Import\TemplateRendering\BlockStructure;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Translation\CsvTranslator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Import\ImageStorage\MediaBaseUrlBuilder;
use LizardsAndPumpkins\Import\ImageStorage\MediaDirectoryBaseUrlBuilder;

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

    /**
     * @var ProductAvailability
     */
    private $memoizedProductAvailability;

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(Message $event)
    {
        return new ProductWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createProductProjector(),
            $this->getMasterFactory()->createProductWasUpdatedDomainEventBuilder()
        );
    }

    /**
     * @return ProductWasUpdatedDomainEventBuilder
     */
    public function createProductWasUpdatedDomainEventBuilder()
    {
        return new ProductWasUpdatedDomainEventBuilder(
            $this->getMasterFactory()->getProductAvailability()
        );
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createTemplateWasUpdatedDomainEventHandler(Message $event)
    {
        return new TemplateWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->getContextSource(),
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
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createProductListingWasAddedDomainEventHandler(Message $event)
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
            $this->getContextSource()
        );
    }

    /**
     * @return SnippetRendererCollection
     */
    public function createProductSnippetRendererCollection()
    {
        return new SnippetRendererCollection(
            $this->createProductDetailPageSnippetRendererList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    public function createProductDetailPageSnippetRendererList()
    {
        return [
            $this->getMasterFactory()->createProductDetailViewSnippetRenderer(),
            $this->getMasterFactory()->createProductInListingSnippetRenderer(),
            $this->getMasterFactory()->createProductInSearchAutosuggestionSnippetRenderer(),
            $this->getMasterFactory()->createPriceSnippetRenderer(),
            $this->getMasterFactory()->createSpecialPriceSnippetRenderer(),
            $this->getMasterFactory()->createProductJsonSnippetRenderer(),
            $this->getMasterFactory()->createConfigurableProductJsonSnippetRenderer(),
            $this->getMasterFactory()->createProductCanonicalTagSnippetRenderer(),
            $this->getMasterFactory()->createProductDetailPageRobotsMetaTagSnippetRenderer(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getContextSource()
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
            $this->getMasterFactory()->getContextSource()
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductSearchAutosuggestionBlockRenderer
     */
    public function createProductSearchAutosuggestionBlockRenderer()
    {
        return new ProductSearchAutosuggestionBlockRenderer(
            $this->getMasterFactory()->getThemeLocator(),
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
            $this->getMasterFactory()->getContextSource()
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
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductListingBlockRenderer
     */
    public function createProductListingBlockRenderer()
    {
        return new ProductListingBlockRenderer(
            $this->getMasterFactory()->getThemeLocator(),
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
            $this->getMasterFactory()->getContextSource()
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
            $this->getMasterFactory()->createProductListingPageRobotsMetaTagSnippetRenderer(),
            $this->getMasterFactory()->createProductListingCanonicalTagSnippetRenderer(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->createContextBuilder(),
            $this->getMasterFactory()->createHtmlHeadMetaKeyGenerator()
        );
    }

    /**
     * @return GenericSnippetKeyGenerator
     */
    public function createHtmlHeadMetaKeyGenerator()
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            ProductListingSnippetRenderer::HTML_HEAD_META_KEY,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getThemeLocator(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            ProductDetailViewSnippetRenderer::TITLE_KEY_CODE,
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductInSearchAutosuggestionBlockRenderer
     */
    public function createProductInSearchAutosuggestionBlockRenderer()
    {
        return new ProductInSearchAutosuggestionBlockRenderer(
            $this->getMasterFactory()->getThemeLocator(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
        return [Website::CONTEXT_CODE, Country::CONTEXT_CODE];
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
        return new SimpleProductXmlToProductBuilder(
            $this->getMasterFactory()->getProductAvailability()
        );
    }

    /**
     * @return ConfigurableProductXmlToProductBuilder
     */
    public function createConfigurableProductXmlToProductBuilder()
    {
        return new ConfigurableProductXmlToProductBuilder(
            $this->getMasterFactory()->createProductXmlToProductBuilderLocatorProxyFactoryMethod(),
            $this->getMasterFactory()->getProductAvailability()
        );
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
    public function getThemeLocator()
    {
        if (null === $this->themeLocator) {
            $this->themeLocator = $this->callExternalCreateMethod('ThemeLocator');
        }

        return $this->themeLocator;
    }

    /**
     * @return ContextSource
     */
    public function getContextSource()
    {
        if (null === $this->contextSource) {
            $this->contextSource = $this->callExternalCreateMethod('ContextSource');
        }

        return $this->contextSource;
    }

    /**
     * @return ContextBuilder
     */
    public function createContextBuilder()
    {
        return new SelfContainedContextBuilder(
            $this->getMasterFactory()->createVersionContextPartBuilder(),
            $this->getMasterFactory()->getWebsiteContextPartBuilder(),
            $this->getMasterFactory()->getCountryContextPartBuilder(),
            $this->getMasterFactory()->getLocaleContextPartBuilder()
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
     * @return ContextPartBuilder
     */
    public function getWebsiteContextPartBuilder()
    {
        if (null === $this->websiteContextPartBuilder) {
            $this->websiteContextPartBuilder = $this->callExternalCreateMethod('WebsiteContextPartBuilder');
        }

        return $this->websiteContextPartBuilder;
    }

    /**
     * @return ContextPartBuilder
     */
    public function getLocaleContextPartBuilder()
    {
        if (null === $this->localeContextPartBuilder) {
            $this->localeContextPartBuilder = $this->callExternalCreateMethod('LocaleContextPartBuilder');
        }

        return $this->localeContextPartBuilder;
    }

    /**
     * @return ContextPartBuilder
     */
    public function getCountryContextPartBuilder()
    {
        if (null === $this->countryContextPartBuilder) {
            $this->countryContextPartBuilder = $this->callExternalCreateMethod('CountryContextPartBuilder');
        }

        return $this->countryContextPartBuilder;
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
            $this->getMasterFactory()->getEventMessageQueue(),
            $this->getMasterFactory()->createDomainEventHandlerLocator(),
            $this->getLogger()
        );
    }

    /**
     * @return DomainEventQueue
     */
    public function getEventQueue()
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->callExternalCreateMethod('EventQueue');
        }

        return $this->eventQueue;
    }

    /**
     * @return Queue
     */
    public function getEventMessageQueue()
    {
        if (null === $this->eventMessageQueue) {
            $this->eventMessageQueue = $this->callExternalCreateMethod('EventMessageQueue');
        }
        return $this->eventMessageQueue;
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
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createImageWasAddedDomainEventHandler(Message $event)
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
            $this->getMasterFactory()->getCommandMessageQueue(),
            $this->getMasterFactory()->createCommandHandlerLocator(),
            $this->getLogger()
        );
    }

    /**
     * @return CommandQueue
     */
    public function getCommandQueue()
    {
        if (null === $this->commandQueue) {
            $this->commandQueue = $this->callExternalCreateMethod('CommandQueue');
        }

        return $this->commandQueue;
    }

    /**
     * @return Queue
     */
    public function getCommandMessageQueue()
    {
        if (null === $this->commandMessageQueue) {
            $this->commandMessageQueue = $this->callExternalCreateMethod('CommandMessageQueue');
        }
        return $this->commandMessageQueue;
    }

    /**
     * @return CommandHandlerLocator
     */
    public function createCommandHandlerLocator()
    {
        return new CommandHandlerLocator($this->getMasterFactory());
    }

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createUpdateContentBlockCommandHandler(Message $message)
    {
        return new UpdateContentBlockCommandHandler(
            $message,
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(Message $event)
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
     * @param Message $message
     * @return CommandHandler
     */
    public function createUpdateProductCommandHandler(Message $message)
    {
        return new UpdateProductCommandHandler(
            $message,
            $this->getMasterFactory()->getEventQueue(),
            $this->getMasterFactory()->createUpdateProductCommandBuilder()
        );
    }

    /**
     * @return UpdateProductCommandBuilder
     */
    public function createUpdateProductCommandBuilder()
    {
        return new UpdateProductCommandBuilder(
            $this->getMasterFactory()->getProductAvailability()
        );
    }

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createAddProductListingCommandHandler(Message $message)
    {
        return new AddProductListingCommandHandler(
            $message,
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createAddImageCommandHandler(Message $message)
    {
        return new AddImageCommandHandler(
            $message,
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @return string[]
     */
    public function getRequiredContextParts()
    {
        return [Website::CONTEXT_CODE, Locale::CONTEXT_CODE, DataVersion::CONTEXT_CODE];
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createContentBlockInProductListingSnippetKeyGenerator()
    {
        return new GenericSnippetKeyGenerator(
            'content_block_in_product_listing',
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getContextSource()
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
            $this->getMasterFactory()->getRequiredContextParts(),
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
            $this->getMasterFactory()->getContextSource(),
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
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->getThemeLocator(), $files);
        };
    }

    /**
     * @return callable
     */
    public function getProductDetailsViewTranslatorFactory()
    {
        return function ($locale) {
            $files = ['common.csv', 'attributes.csv', 'product-details.csv'];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->getThemeLocator(), $files);
        };
    }

    /**
     * @return callable
     */
    public function getProductInSearchAutosuggestionTranslatorFactory()
    {
        return function ($locale) {
            $files = [];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->getThemeLocator(), $files);
        };
    }

    /**
     * @return callable
     */
    public function getProductSearchAutosuggestionTranslatorFactory()
    {
        return function ($locale) {
            $files = ['product_search_autosuggestion.csv'];
            return CsvTranslator::forLocale($locale, $this->getMasterFactory()->getThemeLocator(), $files);
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
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createCatalogWasImportedDomainEventHandler(Message $event)
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
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductListingCanonicalTagSnippetKeyGenerator()
    {
        $usedDataParts = [PageMetaInfoSnippetContent::URL_KEY];

        return new GenericSnippetKeyGenerator(
            ProductListingSnippetRenderer::CANONICAL_TAG_KEY,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductListingDescriptionBlockRenderer
     */
    public function createProductListingDescriptionBlockRenderer()
    {
        return new ProductListingDescriptionBlockRenderer(
            $this->getMasterFactory()->getThemeLocator(),
            $this->getMasterFactory()->createBlockStructure(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            $this->getMasterFactory()->createBaseUrlBuilder()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductDetailPageMetaDescriptionSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            ProductDetailViewSnippetRenderer::HTML_HEAD_META_CODE,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductCanonicalTagSnippetKeyGenerator()
    {
        $usedDataParts = [Product::ID];

        return new GenericSnippetKeyGenerator(
            ProductCanonicalTagSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductCanonicalTagSnippetRenderer
     */
    public function createProductCanonicalTagSnippetRenderer()
    {
        return new ProductCanonicalTagSnippetRenderer(
            $this->getMasterFactory()->createProductCanonicalTagSnippetKeyGenerator(),
            $this->getMasterFactory()->createBaseUrlBuilder()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductDetailPageRobotsMetaTagSnippetKeyGenerator()
    {
        return $this->createRobotsMetaTagSnippetKeyGeneratorForSnippetCode(
            ProductDetailPageRobotsMetaTagSnippetRenderer::CODE
        );
    }

    /**
     * @return ProductDetailPageRobotsMetaTagSnippetRenderer
     */
    public function createProductDetailPageRobotsMetaTagSnippetRenderer()
    {
        $snippetKeyGenerator = $this->getMasterFactory()->createProductDetailPageRobotsMetaTagSnippetKeyGenerator();
        return new ProductDetailPageRobotsMetaTagSnippetRenderer(
            $this->getMasterFactory()->createRobotsMetaTagSnippetRenderer($snippetKeyGenerator)
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductListingPageRobotsMetaTagSnippetKeyGenerator()
    {
        return $this->createRobotsMetaTagSnippetKeyGeneratorForSnippetCode(
            ProductListingRobotsMetaTagSnippetRenderer::CODE
        );
    }

    /**
     * @return ProductListingRobotsMetaTagSnippetRenderer
     */
    public function createProductListingPageRobotsMetaTagSnippetRenderer()
    {
        $snippetKeyGenerator = $this->getMasterFactory()->createProductListingPageRobotsMetaTagSnippetKeyGenerator();
        return new ProductListingRobotsMetaTagSnippetRenderer(
            $this->getMasterFactory()->createRobotsMetaTagSnippetRenderer($snippetKeyGenerator),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    /**
     * @param string $code
     * @return SnippetKeyGenerator
     */
    private function createRobotsMetaTagSnippetKeyGeneratorForSnippetCode($code)
    {
        $usedDataParts = ['robots'];

        return new GenericSnippetKeyGenerator(
            $code,
            $this->getMasterFactory()->getRequiredContextParts(),
            $usedDataParts
        );
    }

    /**
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @return RobotsMetaTagSnippetRenderer
     */
    public function createRobotsMetaTagSnippetRenderer(SnippetKeyGenerator $snippetKeyGenerator)
    {
        return new RobotsMetaTagSnippetRenderer($snippetKeyGenerator);
    }

    /**
     * @return FacetFieldTransformationRegistry
     */
    public function getProductAvailability()
    {
        if (null === $this->memoizedProductAvailability) {
            $this->memoizedProductAvailability = $this->callExternalCreateMethod('ProductAvailability');
        }

        return $this->memoizedProductAvailability;
    }

    /**
     * @return ProductListingCanonicalTagSnippetRenderer
     */
    public function createProductListingCanonicalTagSnippetRenderer()
    {
        return new ProductListingCanonicalTagSnippetRenderer(
            $this->getMasterFactory()->createProductListingCanonicalTagSnippetKeyGenerator(),
            $this->getMasterFactory()->createBaseUrlBuilder(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }
}
