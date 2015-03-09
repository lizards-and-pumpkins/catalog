<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Context\ContextSource;
use Brera\Context\ContextSourceBuilder;
use Brera\Http\ResourceNotFoundRouter;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductDetailViewBlockRenderer;
use Brera\Product\ProductInContextDetailViewSnippetRenderer;
use Brera\Product\ProductListingBlockRenderer;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Product\ProductSearchDocumentBuilder;
use Brera\Product\ProductSnippetRendererCollection;
use Brera\Product\ProductSourceBuilder;
use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\Queue\Queue;
use Brera\DataPool\DataPoolWriter;
use Brera\DataPool\DataPoolReader;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSourceDetailViewSnippetRenderer;
use Brera\Product\ProductDetailViewSnippetKeyGenerator;
use Brera\Renderer\BlockStructure;
use Brera\Http\HttpRouterChain;

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
     * @var SnippetKeyGeneratorLocator
     */
    private $snippetKeyGeneratorLocator;

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
     * @return RootSnippetSourceBuilder
     */
    public function createRootSnippetSourceBuilder()
    {
        return new RootSnippetSourceBuilder();
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
            $this->getMasterFactory()->createProductInContextDetailViewSnippetRenderer()
        );
    }

    /**
     * @return ProductInContextDetailViewSnippetRenderer
     * @todo: move to catalog factory
     */
    public function createProductInContextDetailViewSnippetRenderer()
    {
        return new ProductInContextDetailViewSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductDetailViewBlockRenderer(),
            $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator(),
            $this->getMasterFactory()->createUrlPathKeyGenerator()
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
     * @return ProductDetailViewSnippetKeyGenerator
     * @todo: move to catalog factory
     */
    public function createProductDetailViewSnippetKeyGenerator()
    {
        return new ProductDetailViewSnippetKeyGenerator();
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
     * @return SnippetKeyGeneratorLocator
     */
    public function createSnippetKeyGeneratorLocator()
    {
        return new SnippetKeyGeneratorLocator();
    }

    /**
     * @return SnippetKeyGeneratorLocator
     */
    public function getSnippetKeyGeneratorLocator()
    {
        if (is_null($this->snippetKeyGeneratorLocator)) {
            $this->snippetKeyGeneratorLocator = $this->createSnippetKeyGeneratorLocator();
        }
        return $this->snippetKeyGeneratorLocator;
    }

    /**
     * @return SearchEngine
     */
    private function getSearchEngine()
    {
        if (is_null($this->searchEngine)) {
            $this->searchEngine = $this->callExternalCreateMethod('SearchEngine');
        }

        return $this->searchEngine;
    }
}
