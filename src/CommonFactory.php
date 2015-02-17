<?php

namespace Brera;

use Brera\Environment\EnvironmentBuilder;
use Brera\Environment\EnvironmentSourceBuilder;
use Brera\Http\ResourceNotFoundRouter;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
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
use Brera\Product\ProductDetailViewSnippetRenderer;
use Brera\Product\ProductDetailViewSnippetKeyGenerator;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

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
            $this->getMasterFactory()->createEnvironmentSourceBuilder(),
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
            $this->getMasterFactory()->createProductDetailViewSnippetRenderer(),
        ];
    }

    /**
     * @return SnippetResultList
     */
    public function createSnippetResultList()
    {
        return new SnippetResultList();
    }

    /**
     * @return ProductDetailViewSnippetRenderer
     * @todo: move to catalog factory
     */
    public function createProductDetailViewSnippetRenderer()
    {
        return new ProductDetailViewSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator(),
            $this->getMasterFactory()->createUrlPathKeyGenerator(),
            $this->getMasterFactory()->createThemeLocator()
        );
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
     * @return EnvironmentSourceBuilder
     */
    public function createEnvironmentSourceBuilder()
    {
        return new EnvironmentSourceBuilder($this->getMasterFactory()->createEnvironmentBuilder());
    }

    /**
     * @return EnvironmentBuilder
     */
    public function createEnvironmentBuilder()
    {
        $version = $this->getCurrentDataVersion();
        return $this->createEnvironmentBuilderWithVersion(DataVersion::fromVersionString($version));
    }

    /**
     * @param DataVersion $version
     * @return EnvironmentBuilder
     */
    public function createEnvironmentBuilderWithVersion(DataVersion $version)
    {
        return new EnvironmentBuilder($version);
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
     * @return LoggerInterface
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
        if (is_null($this->searchEngine)) {
            $this->searchEngine = $this->callExternalCreateMethod('SearchEngine');
        }

        return $this->searchEngine;
    }
}
