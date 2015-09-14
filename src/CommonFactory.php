<?php

namespace Brera;

use Brera\Content\ContentBlockProjector;
use Brera\Content\ContentBlockSnippetRenderer;
use Brera\Content\ContentBlockWasUpdatedDomainEvent;
use Brera\Content\ContentBlockWasUpdatedDomainEventHandler;
use Brera\Content\UpdateContentBlockCommand;
use Brera\Content\UpdateContentBlockCommandHandler;
use Brera\Context\ContextBuilder;
use Brera\Context\ContextSource;
use Brera\Context\LocaleContextDecorator;
use Brera\Context\WebsiteContextDecorator;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\DataPoolWriter;
use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Http\HttpRouterChain;
use Brera\Http\ResourceNotFoundRouter;
use Brera\Image\ImageWasUpdatedDomainEvent;
use Brera\Image\ImageWasUpdatedDomainEventHandler;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\UpdateImageCommand;
use Brera\Image\UpdateImageCommandHandler;
use Brera\Log\Logger;
use Brera\Product\DefaultNumberOfProductsPerPageSnippetRenderer;
use Brera\Product\FilterNavigationBlockRenderer;
use Brera\Product\FilterNavigationFilterCollection;
use Brera\Product\PriceSnippetRenderer;
use Brera\Product\ProductBackOrderAvailabilitySnippetRenderer;
use Brera\Product\ProductDetailViewBlockRenderer;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductInSearchAutosuggestionBlockRenderer;
use Brera\Product\ProductInSearchAutosuggestionSnippetRenderer;
use Brera\Product\ProductListingSourceListBuilder;
use Brera\Product\ProductListingTemplateProjector;
use Brera\Product\ProductSearchAutosuggestionBlockRenderer;
use Brera\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use Brera\Product\ProductSearchAutosuggestionSnippetRenderer;
use Brera\Product\ProductSearchAutosuggestionTemplateProjector;
use Brera\Product\ProductWasUpdatedDomainEvent;
use Brera\Product\ProductWasUpdatedDomainEventHandler;
use Brera\Product\ProductInListingBlockRenderer;
use Brera\Product\ProductListingBlockRenderer;
use Brera\Product\ProductListingMetaInfoSnippetRenderer;
use Brera\Product\ProductListingMetaInfoSnippetProjector;
use Brera\Product\ProductListingWasUpdatedDomainEvent;
use Brera\Product\ProductListingWasUpdatedDomainEventHandler;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Product\ProductProjector;
use Brera\Product\ProductListingMetaInfoSourceBuilder;
use Brera\Product\ProductSearchDocumentBuilder;
use Brera\Product\ProductSearchResultMetaSnippetRenderer;
use Brera\Product\ProductSourceBuilder;
use Brera\Product\ProductSourceDetailViewSnippetRenderer;
use Brera\Product\ProductInListingSnippetRenderer;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use Brera\Product\ProductStockQuantityProjector;
use Brera\Product\ProductStockQuantitySnippetRenderer;
use Brera\Product\ProductStockQuantitySourceBuilder;
use Brera\Product\UpdateMultipleProductStockQuantityCommand;
use Brera\Product\UpdateMultipleProductStockQuantityCommandHandler;
use Brera\Product\UpdateProductCommand;
use Brera\Product\UpdateProductCommandHandler;
use Brera\Product\UpdateProductListingCommand;
use Brera\Product\UpdateProductListingCommandHandler;
use Brera\Product\UpdateProductStockQuantityCommand;
use Brera\Product\UpdateProductStockQuantityCommandHandler;
use Brera\Projection\Catalog\Import\CatalogImport;
use Brera\Projection\ProcessTimeLoggingDomainEventHandlerDecorator;
use Brera\Queue\Queue;
use Brera\Renderer\BlockStructure;

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
     * @param ProductWasUpdatedDomainEvent $event
     * @return ProductWasUpdatedDomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(ProductWasUpdatedDomainEvent $event)
    {
        return new ProductWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createContextSource(),
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
            ProductListingSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductListingTemplateProjector()
        );
        $templateProjectorLocator->register(
            ProductSearchAutosuggestionSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductSearchAutosuggestionTemplateProjector()
        );

        return $templateProjectorLocator;
    }

    /**
     * @param ProductListingWasUpdatedDomainEvent $event
     * @return ProductListingWasUpdatedDomainEventHandler
     */
    public function createProductListingWasUpdatedDomainEventHandler(ProductListingWasUpdatedDomainEvent $event)
    {
        return new ProductListingWasUpdatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->createProductListingMetaInfoSnippetProjector()
        );
    }

    /**
     * @return ProductListingSourceListBuilder
     */
    public function createProductListingSourceListBuilder()
    {
        return new ProductListingSourceListBuilder($this->getMasterFactory()->createContextBuilder());
    }

    /**
     * @return ProductListingMetaInfoSourceBuilder
     */
    public function createProductListingMetaInfoSourceBuilder()
    {
        return new ProductListingMetaInfoSourceBuilder();
    }

    /**
     * @return ProductProjector
     */
    public function createProductProjector()
    {
        return new ProductProjector(
            $this->createProductSnippetRendererCollection(),
            $this->createProductSearchDocumentBuilder(),
            $this->getMasterFactory()->createDataPoolWriter()
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
            $this->getMasterFactory()->createProductSourceDetailViewSnippetRenderer(),
            $this->getMasterFactory()->createProductInListingSnippetRenderer(),
            $this->getMasterFactory()->createProductInSearchAutosuggestionSnippetRenderer(),
            $this->getMasterFactory()->createPriceSnippetRenderer(),
            $this->getMasterFactory()->createProductBackOrderAvailabilitySnippetRenderer()
        ];
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
            $this->getMasterFactory()->createProductSearchAutosuggestionBlockRenderer()
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
            $this->getMasterFactory()->createProductSearchAutosuggestionBlockRenderer()
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
            $this->getMasterFactory()->createBlockStructure()
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
            $this->getMasterFactory()->createProductListingSourceListBuilder()
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
            $this->getMasterFactory()->createProductListingSnippetRenderer(),
            $this->getMasterFactory()->createDefaultNumberOfProductsPerPageSnippetRenderer(),
            $this->getMasterFactory()->createProductSearchResultMetaSnippetRenderer(),
        ];
    }

    /**
     * @return ProductListingSnippetRenderer
     */
    public function createProductListingSnippetRenderer()
    {
        return new ProductListingSnippetRenderer(
            $this->getMasterFactory()->createSnippetList(),
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
            ProductListingSnippetRenderer::CODE,
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
            $this->getMasterFactory()->createBlockStructure()
        );
    }

    /**
     * @return DefaultNumberOfProductsPerPageSnippetRenderer
     */
    public function createDefaultNumberOfProductsPerPageSnippetRenderer()
    {
        return new DefaultNumberOfProductsPerPageSnippetRenderer(
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createDefaultNumberOfProductsPerPageSnippetKeyGenerator()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createDefaultNumberOfProductsPerPageSnippetKeyGenerator()
    {
        $usedDataParts = [];

        return new GenericSnippetKeyGenerator(
            DefaultNumberOfProductsPerPageSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductListingMetaInfoSnippetProjector
     */
    public function createProductListingMetaInfoSnippetProjector()
    {
        return new ProductListingMetaInfoSnippetProjector(
            $this->getMasterFactory()->createProductListingSnippetRendererCollection(),
            $this->getMasterFactory()->createDataPoolWriter()
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
            $this->getMasterFactory()->createProductListingMetaInfoSnippetRenderer()
        ];
    }

    /**
     * @return ProductListingMetaInfoSnippetRenderer
     */
    public function createProductListingMetaInfoSnippetRenderer()
    {
        return new ProductListingMetaInfoSnippetRenderer(
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createProductListingBlockRenderer(),
            $this->getMasterFactory()->createProductListingMetaDataSnippetKeyGenerator(),
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
    public function createProductListingMetaDataSnippetKeyGenerator()
    {
        $usedDataParts = ['url_key'];

        return new GenericSnippetKeyGenerator(
            ProductListingMetaInfoSnippetRenderer::CODE,
            $this->getMasterFactory()->getRequiredContexts(),
            $usedDataParts
        );
    }

    /**
     * @return ProductSourceDetailViewSnippetRenderer
     */
    public function createProductSourceDetailViewSnippetRenderer()
    {
        return new ProductSourceDetailViewSnippetRenderer(
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createProductDetailViewInContextSnippetRenderer()
        );
    }

    /**
     * @return ProductDetailViewInContextSnippetRenderer
     */
    public function createProductDetailViewInContextSnippetRenderer()
    {
        return new ProductDetailViewInContextSnippetRenderer(
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
            $this->getMasterFactory()->createBlockStructure()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductDetailViewSnippetKeyGenerator()
    {
        $usedDataParts = ['product_id'];

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
        $usedDataParts = ['url_key'];

        return new GenericSnippetKeyGenerator(
            ProductDetailViewInContextSnippetRenderer::CODE,
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
            $this->getMasterFactory()->createSnippetList(),
            $this->getMasterFactory()->createProductInListingBlockRenderer(),
            $this->getMasterFactory()->createProductInListingSnippetKeyGenerator()
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
     * @return ProductInListingBlockRenderer
     */
    public function createProductInListingBlockRenderer()
    {
        return new ProductInListingBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductInListingSnippetKeyGenerator()
    {
        $usedDataParts = ['product_id'];

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
            $this->getMasterFactory()->createBlockStructure()
        );
    }

    /**
     * @return SnippetKeyGenerator
     */
    public function createProductInSearchAutosuggestionSnippetKeyGenerator()
    {
        $usedDataParts = ['product_id'];

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
        $usedDataParts = ['product_id'];

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
        $usedDataParts = ['product_id'];

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
     * @return ProductSourceBuilder
     */
    public function createProductSourceBuilder()
    {
        return new ProductSourceBuilder();
    }

    /**
     * @return ThemeLocator
     */
    public function createThemeLocator()
    {
        return new ThemeLocator();
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
        /** @var DataPoolReader $dataPoolReader */
        $dataPoolReader = $this->getMasterFactory()->createDataPoolReader();

        return $dataPoolReader->getCurrentDataVersion();
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
            $this->getMasterFactory()->getSearchEngine()
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
            $this->getMasterFactory()->getSearchEngine()
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
            $this->getMasterFactory()->getProductListingFilterNavigationAttributeCodes(),
            $this->getMasterFactory()->getProductSearchResultsFilterNavigationAttributeCodes()
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
     * @param ImageWasUpdatedDomainEvent $event
     * @return ImageWasUpdatedDomainEventHandler
     */
    public function createImageWasUpdatedDomainEventHandler(ImageWasUpdatedDomainEvent $event)
    {
        return new ImageWasUpdatedDomainEventHandler(
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
        $usedDataParts = ['product_id'];

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
     * @param UpdateProductListingCommand $command
     * @return UpdateProductListingCommandHandler
     */
    public function createUpdateProductListingCommandHandler(UpdateProductListingCommand $command)
    {
        return new UpdateProductListingCommandHandler(
            $command,
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @param UpdateImageCommand $command
     * @return UpdateImageCommandHandler
     */
    public function createUpdateImageCommandHandler(UpdateImageCommand $command)
    {
        return new UpdateImageCommandHandler(
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
            ['url_key']
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
            $this->getMasterFactory()->createProductListingBlockRenderer()
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
     * @return FilterNavigationBlockRenderer
     */
    public function createFilterNavigationBlockRenderer()
    {
        return new FilterNavigationBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure()
        );
    }

    /**
     * @return FilterNavigationFilterCollection
     */
    public function createFilterNavigationFilterCollection()
    {
        return new FilterNavigationFilterCollection(
            $this->getMasterFactory()->createDataPoolReader()
        );
    }

    /**
     * @return PaginationBlockRenderer
     */
    public function createPaginationBlockRenderer()
    {
        return new PaginationBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure()
        );
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
            $this->getMasterFactory()->createProductSourceBuilder(),
            $this->getMasterFactory()->createProductListingMetaInfoSourceBuilder(),
            $this->getMasterFactory()->getLogger()
        );
    }
}
