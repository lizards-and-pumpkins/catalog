<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Context\ContextSource;
use Brera\Http\ResourceNotFoundRouter;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductDetailViewBlockRenderer;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductInListingInContextSnippetRenderer;
use Brera\Product\ProductInListingBlockRenderer;
use Brera\Product\ProductListingBlockRenderer;
use Brera\Product\ProductListingCriteriaSnippetRenderer;
use Brera\Product\ProductListingProjector;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSavedDomainEventHandler;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Product\ProductListingSourceBuilder;
use Brera\Product\ProductSearchDocumentBuilder;
use Brera\Product\ProductSnippetKeyGenerator;
use Brera\Product\ProductSnippetRendererCollection;
use Brera\Product\ProductSourceBuilder;
use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\Product\ProductSourceInListingSnippetRenderer;
use Brera\Queue\Queue;
use Brera\DataPool\DataPoolWriter;
use Brera\DataPool\DataPoolReader;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSourceDetailViewSnippetRenderer;
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
     * @param ProductImportDomainEvent $event
     * @return ProductImportDomainEventHandler
     */
    public function createProductImportDomainEventHandler(ProductImportDomainEvent $event)
    {
        // TODO move to catalog factory
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
     */
    public function createCatalogImportDomainEventHandler(CatalogImportDomainEvent $event)
    {
        // TODO move to catalog factory
        return new CatalogImportDomainEventHandler($event, $this->getMasterFactory()->getEventQueue());
    }

    /**
     * @param RootTemplateChangedDomainEvent $event
     * @return RootTemplateChangedDomainEventHandler
     */
    public function createRootTemplateChangedDomainEventHandler(RootTemplateChangedDomainEvent $event)
    {
        // TODO move to catalog factory
        return new RootTemplateChangedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createRootSnippetSourceBuilder(),
            $this->getMasterFactory()->createContextSource(),
            $this->getMasterFactory()->createRootSnippetProjector()
        );
    }

    /**
     * @param ProductListingSavedDomainEvent $event
     * @return ProductListingSavedDomainEventHandler
     */
    public function createProductListingSavedDomainEventHandler(ProductListingSavedDomainEvent $event)
    {
        // TODO move to catalog factory
        return new ProductListingSavedDomainEventHandler(
            $event,
            $this->getMasterFactory()->createProductListingSourceBuilder(),
            $this->getMasterFactory()->createProductListingProjector()
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
     * @return ProductListingSourceBuilder
     */
    public function createProductListingSourceBuilder()
    {
        return new ProductListingSourceBuilder();
    }

    /**
     * @return ProductProjector
     */
    public function createProductProjector()
    {
        // TODO move to catalog factory
        return new ProductProjector(
            $this->createProductSnippetRendererCollection(),
            $this->createProductSearchDocumentBuilder(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    /**
     * @return ProductSnippetRendererCollection
     */
    public function createProductSnippetRendererCollection()
    {
        // TODO move to catalog factory
        return new ProductSnippetRendererCollection(
            $this->getProductSnippetRendererList(),
            $this->getMasterFactory()->createSnippetResultList()
        );
    }

    /**
     * @return SnippetRenderer[]
     */
    private function getProductSnippetRendererList()
    {
        // TODO move to catalog factory
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
     * @return ProductListingProjector
     */
    public function createProductListingProjector()
    {
        return new ProductListingProjector(
            $this->getMasterFactory()->createProductListingPageMetaInfoSnippetRenderer(),
            $this->getMasterFactory()->createDataPoolWriter()
        );
    }

    /**
     * @return ProductListingCriteriaSnippetRenderer
     */
    public function createProductListingPageMetaInfoSnippetRenderer()
    {
        return new ProductListingCriteriaSnippetRenderer(
            $this->getMasterFactory()->createUrlPathKeyGenerator(),
            $this->getMasterFactory()->createContextBuilder()
        );
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
        return new GenericSnippetKeyGenerator(
            ProductListingSnippetRenderer::CODE,
            ['website', 'language', 'version']
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
     * @return SnippetResultList
     */
    public function createSnippetResultList()
    {
        return new SnippetResultList();
    }

    /**
     * @return ProductSourceDetailViewSnippetRenderer
     */
    public function createProductSourceDetailViewSnippetRenderer()
    {
        // TODO move to catalog factory
        return new ProductSourceDetailViewSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductDetailViewInContextSnippetRenderer()
        );
    }

    /**
     * @return ProductDetailViewInContextSnippetRenderer
     */
    public function createProductDetailViewInContextSnippetRenderer()
    {
        // TODO move to catalog factory
        return new ProductDetailViewInContextSnippetRenderer(
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
        // TODO move to catalog factory
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
        // TODO move to catalog factory
        return new ProductSnippetKeyGenerator('product_detail_view');
    }

    /**
     * @return ProductSourceInListingSnippetRenderer
     */
    public function createProductSourceInListingSnippetRenderer()
    {
        // TODO move to catalog factory
        return new ProductSourceInListingSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductInListingInContextSnippetRenderer()
        );
    }

    /**
     * @return ProductInListingInContextSnippetRenderer
     */
    public function createProductInListingInContextSnippetRenderer()
    {
        // TODO move to catalog factory
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
        // TODO move to catalog factory
        return new ProductSnippetKeyGenerator(
            ProductInListingInContextSnippetRenderer::CODE
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
     * @return ProductSourceBuilder
     */
    public function createProductSourceBuilder()
    {
        // TODO move to catalog factory
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
        // TODO: Move to sample factory
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
}
