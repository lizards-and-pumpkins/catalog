<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\DataPoolWriter;
use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Http\HttpRouterChain;
use Brera\Http\ResourceNotFoundRouter;
use Brera\ImageImport\ImageProcessCommandSequence;
use Brera\ImageImport\ImageProcessConfiguration;
use Brera\ImageImport\ImportImageDomainEvent;
use Brera\ImageImport\ImportImageDomainEventHandler;
use Brera\ImageProcessor\ImageMagickImageProcessor;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductDetailViewBlockRenderer;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductInListingBlockRenderer;
use Brera\Product\ProductInListingInContextSnippetRenderer;
use Brera\Product\ProductListingBlockRenderer;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSearchDocumentBuilder;
use Brera\Product\ProductSnippetRendererCollection;
use Brera\Product\ProductSourceBuilder;
use Brera\Product\ProductSourceDetailViewSnippetRenderer;
use Brera\Product\ProductSourceInListingSnippetRenderer;
use Brera\Queue\Queue;
use Brera\Renderer\BlockStructure;

class CommonFactory implements Factory, DomainEventFactory
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
     * @var Logger
     */
    private $logger;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var ImageProcessConfiguration
     */
    private $imageProcessingConfiguration;

    /**
     * @param ProductImportDomainEvent $event
     * @return ProductImportDomainEventHandler
     * @todo: move to catalog factory
     */
    public function createProductImportDomainEventHandler(ProductImportDomainEvent $event)
    {
        return new ProductImportDomainEventHandler(
            $event,
            $this->getMasterFactory()->createProductSourceBuilder(),
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->createProductProjector(),
            $this->getMasterFactory()->createProductSearchDocumentBuilder()
        );
    }

    /**
     * @param CatalogImportDomainEvent $event
     * @return CatalogImportDomainEventHandler
     * @todo: move to catalog factory
     */
    public function createCatalogImportDomainEventHandler(CatalogImportDomainEvent $event)
    {
        return new CatalogImportDomainEventHandler($event, $this->getMasterFactory()->getEventQueue());
    }

    /**
     * @param RootTemplateChangedDomainEvent $event
     * @return RootTemplateChangedDomainEventHandler
     * @todo: move to catalog factory
     */
    public function createRootTemplateChangedDomainEventHandler(RootTemplateChangedDomainEvent $event)
    {
        return new RootTemplateChangedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createRootSnippetSourceBuilder(),
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->createRootSnippetProjector()
        );
    }

    /**
     * @return RootSnippetSourceListBuilder
     */
    public function createRootSnippetSourceBuilder()
    {
        return new RootSnippetSourceListBuilder($this->getMasterFactory()->createContextBuilder());
    }

    /**
     * @return ProductProjector
     * @todo: move to catalog factory
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
     * @return ProductSnippetRendererCollection
     * @todo: move to catalog factory
     */
    public function createProductSnippetRendererCollection()
    {
        return new ProductSnippetRendererCollection(
            $this->getProductSnippetRendererList(),
            $this->getMasterFactory()->createSnippetResultList()
        );
    }

    /**
     * @return SnippetRenderer[]
     * @todo: move to catalog factory
     */
    private function getProductSnippetRendererList()
    {
        return [
            $this->getMasterFactory()->createProductSourceDetailViewSnippetRenderer(),
            $this->getMasterFactory()->createProductSourceInListingSnippetRenderer()
        ];
    }

    /**
     * @return RootSnippetProjector
     */
    public function createRootSnippetProjector()
    {
        return new RootSnippetProjector(
            $this->createRootSnippetRendererCollection(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    /**
     * @return RootSnippetRendererCollection
     */
    public function createRootSnippetRendererCollection()
    {
        return new RootSnippetRendererCollection(
            $this->getRootSnippetRendererList(),
            $this->getMasterFactory()->createSnippetResultList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    private function getRootSnippetRendererList()
    {
        return [
            $this->getMasterFactory()->createProductListingSnippetRenderer(),
        ];
    }

    /**
     * @return ProductListingSnippetRenderer
     */
    public function createProductListingSnippetRenderer()
    {
        return new ProductListingSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductListingSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductListingBlockRenderer()
        );
    }

    /**
     * @return GenericSnippetKeyGenerator
     */
    public function createProductListingSnippetKeyGenerator()
    {
        return new GenericSnippetKeyGenerator('product_listing', ['website', 'language', 'version']);
    }

    public function createProductListingBlockRenderer()
    {
        return new ProductListingBlockRenderer(
            $this->getMasterFactory()->createThemeLocator(),
            $this->getMasterFactory()->createBlockStructure()
        );
    }

    /**
     * @return SnippetResultList
     */
    public function createSnippetResultList()
    {
        return new SnippetResultList();
    }

    /**
     * @return ProductSourceDetailViewSnippetRenderer
     * @todo: move to catalog factory
     */
    public function createProductSourceDetailViewSnippetRenderer()
    {
        return new ProductSourceDetailViewSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductDetailViewInContextSnippetRenderer()
        );
    }

    /**
     * @return ProductDetailViewInContextSnippetRenderer
     * @todo: move to catalog factory
     */
    public function createProductDetailViewInContextSnippetRenderer()
    {
        return new ProductDetailViewInContextSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductDetailViewBlockRenderer(),
            $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator(),
            $this->getMasterFactory()->createUrlPathKeyGenerator()
        );
    }

    /**
     * @return ProductDetailViewBlockRenderer
     * @todo: move to catalog factory
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
     * @todo: move to catalog factory
     */
    public function createProductDetailViewSnippetKeyGenerator()
    {
        return new GenericSnippetKeyGenerator('product_detail_view', ['website', 'language']);
    }

    /**
     * @return ProductSourceInListingSnippetRenderer
     * @todo: move to catalog factory
     */
    public function createProductSourceInListingSnippetRenderer()
    {
        return new ProductSourceInListingSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductInContextInListingSnippetRenderer()
        );
    }

    /**
     * @return ProductInListingInContextSnippetRenderer
     * @todo: move to catalog factory
     */
    public function createProductInContextInListingSnippetRenderer()
    {
        return new ProductInListingInContextSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductInListingBlockRenderer(),
            $this->getMasterFactory()->createProductInListingSnippetKeyGenerator()
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
     * @todo: move to catalog factory
     */
    public function createProductInListingSnippetKeyGenerator()
    {
        return new GenericSnippetKeyGenerator('product_in_listing', ['website', 'language']);
    }

    /**
     * @return BlockStructure
     */
    public function createBlockStructure()
    {
        return new BlockStructure();
    }

    /**
     * @return PoCUrlPathKeyGenerator
     */
    public function createUrlPathKeyGenerator()
    {
        return new PoCUrlPathKeyGenerator();
    }

    /**
     * @return ProductSourceBuilder
     * @todo: move to catalog factory
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
        /* TODO: Move to sample factory */

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
        return new ContextBuilder($version);
    }

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
        return new DomainEventHandlerLocator($this);
    }

    /**
     * @return DataPoolWriter
     */
    public function createDataPoolWriter()
    {
        return new DataPoolWriter($this->getKeyValueStore(), $this->getSearchEngine());
    }

    /**
     * @return KeyValueStore
     * @throws UndefinedFactoryMethodException
     */
    private function getKeyValueStore()
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
     * @throws UndefinedFactoryMethodException
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
        return new DataPoolReader($this->getKeyValueStore(), $this->getSearchEngine());
    }

    /**
     * @return Logger
     * @throws UndefinedFactoryMethodException
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
     * @throws UndefinedFactoryMethodException
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
        return new ProductSearchDocumentBuilder($this->getMasterFactory()->getSearchableAttributeCodes());
    }

    /**
     * @return SearchEngine
     */
    private function getSearchEngine()
    {
        if (null === $this->searchEngine) {
            $this->searchEngine = $this->callExternalCreateMethod('SearchEngine');
        }

        return $this->searchEngine;
    }

    /**
     * @param ImportImageDomainEvent $event
     * @return ImportImageDomainEventHandler
     */
    public function createImportImageDomainEventHandler(ImportImageDomainEvent $event)
    {
        $config = $this->getImageProcessingConfiguration();
        $imageProcessor = $this->createImageProcessor();

        return new ImportImageDomainEventHandler($config, $event, $imageProcessor);
    }

    /**
     * @return ImageProcessConfiguration
     */
    private function getImageProcessingConfiguration()
    {
        // TODO get config from somewhere and remove hardcoded
        if (!$this->imageProcessingConfiguration) {
            $commands1 = [
                'resize' => [400, 800],
            ];
            $commands2 = [
                'resizeToWidth' => [200],
            ];
            $commands3 = [
                'resizeToBestFit' => [400, 300],
            ];
            $configuration = [
                ImageProcessCommandSequence::fromArray($commands1),
                ImageProcessCommandSequence::fromArray($commands2),
                ImageProcessCommandSequence::fromArray($commands3),
            ];
            $targetDirectory = sys_get_temp_dir() . '/brera/';
            // TODO make sure directory exists, not sure whether this should stay here, if it is not writeable
            // TODO exception is thrown
            if(!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }

            $this->imageProcessingConfiguration = new ImageProcessConfiguration($configuration, $targetDirectory);
        }

        return $this->imageProcessingConfiguration;
    }

    /**
     * @return ImageMagickImageProcessor
     */
    private function createImageProcessor()
    {
        // TODO get from master factory
        return ImageMagickImageProcessor::fromNothing();
    }
}
