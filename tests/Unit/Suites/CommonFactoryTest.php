<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Context\ContextSource;
use Brera\Http\ResourceNotFoundRouter;
use Brera\Http\HttpRouterChain;
use Brera\DataPool\DataPoolReader;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSavedDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSourceBuilder;
use Brera\Queue\Queue;

/**
 * @covers \Brera\CommonFactory
 * @covers \Brera\FactoryTrait
 * @uses   \Brera\DataVersion
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\DataPool\DataPoolWriter
 * @uses   \Brera\DataPool\DataPoolReader
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\ContextSource
 * @uses   \Brera\DomainEventConsumer
 * @uses   \Brera\DomainEventHandlerLocator
 * @uses   \Brera\RootTemplateChangedDomainEvent
 * @uses   \Brera\RootTemplateChangedDomainEventHandler
 * @uses   \Brera\RootSnippetProjector
 * @uses   \Brera\UrlPathKeyGenerator
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\Product\ProductProjector
 * @uses   \Brera\Product\ProductSnippetRendererCollection
 * @uses   \Brera\Product\ProductImportDomainEvent
 * @uses   \Brera\Product\ProductImportDomainEventHandler
 * @uses   \Brera\Product\ProductListingCriteriaSnippetRenderer
 * @uses   \Brera\Product\ProductListingProjector
 * @uses   \Brera\Product\ProductListingSavedDomainEvent
 * @uses   \Brera\Product\ProductListingSavedDomainEventHandler
 * @uses   \Brera\Product\CatalogImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEventHandler
 * @uses   \Brera\Product\ProductSearchDocumentBuilder
 * @uses   \Brera\Product\ProductSourceDetailViewSnippetRenderer
 * @uses   \Brera\Product\ProductDetailViewBlockRenderer
 * @uses   \Brera\Product\ProductDetailViewInContextSnippetRenderer
 * @uses   \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\RootSnippetRendererCollection
 * @uses   \Brera\RootSnippetSourceListBuilder
 * @uses   \Brera\Product\ProductSourceInListingSnippetRenderer
 * @uses   \Brera\Product\ProductInListingInContextSnippetRenderer
 */
class CommonFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommonFactory
     */
    private $commonFactory;

    protected function setUp()
    {
        $masterFactory = new PoCMasterFactory();
        $masterFactory->register(new IntegrationTestFactory());
        $this->commonFactory = new CommonFactory();
        $masterFactory->register($this->commonFactory);
    }

    /**
     * @test
     * @expectedException \Brera\NoMasterFactorySetException
     */
    public function itShouldThrowAnExceptionIfNoMasterFactoryIsSet()
    {
        (new CommonFactory())->createDomainEventConsumer();
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateAProductImportDomainEventHandler()
    {
        $productImportDomainEvent = new ProductImportDomainEvent('<xml/>');
        $result = $this->commonFactory->createProductImportDomainEventHandler($productImportDomainEvent);
        $this->assertInstanceOf(ProductImportDomainEventHandler::class, $result);
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateACatalogImportDomainEventHandler()
    {
        $catalogImportDomainEvent = new CatalogImportDomainEvent('<xml/>');
        $result = $this->commonFactory->createCatalogImportDomainEventHandler($catalogImportDomainEvent);
        $this->assertInstanceOf(CatalogImportDomainEventHandler::class, $result);
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateARootTemplateChangedDomainEventHandler()
    {
        $rootTemplateChangedDomainEvent = new RootTemplateChangedDomainEvent('<xml/>');
        $result = $this->commonFactory->createRootTemplateChangedDomainEventHandler($rootTemplateChangedDomainEvent);
        $this->assertInstanceOf(RootTemplateChangedDomainEventHandler::class, $result);
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateAProductListingSavedDomainEventHandler()
    {
        $productListingSavedDomainEvent = new ProductListingSavedDomainEvent('<xml/>');
        $result = $this->commonFactory->createProductListingSavedDomainEventHandler($productListingSavedDomainEvent);
        $this->assertInstanceOf(ProductListingSavedDomainEventHandler::class, $result);
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateAProductProjector()
    {
        $result = $this->commonFactory->createProductProjector();
        $this->assertInstanceOf(ProductProjector::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateAnUrlPathKeyGenerator()
    {
        $result = $this->commonFactory->createUrlPathKeyGenerator();
        $this->assertInstanceOf(UrlPathKeyGenerator::class, $result);
    }
    

    /**
     * @test
     */
    public function itShouldCreateSnippetResultList()
    {
        $result = $this->commonFactory->createSnippetResultList();
        $this->assertInstanceOf(SnippetResultList::class, $result);
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateProductDetailViewSnippetSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductDetailViewSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldReturnProductBuilder()
    {
        $result = $this->commonFactory->createProductSourceBuilder();
        $this->assertInstanceOf(ProductSourceBuilder::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnThemeLocator()
    {
        $result = $this->commonFactory->createThemeLocator();
        $this->assertInstanceOf(ThemeLocator::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateAnContextSource()
    {
        $result = $this->commonFactory->createContextSource();

        $this->assertInstanceOf(ContextSource::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateAnContextBuilder()
    {
        $result = $this->commonFactory->createContextBuilder();
        $this->assertInstanceOf(ContextBuilder::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateADomainEventHandlerLocator()
    {
        $result = $this->commonFactory->createDomainEventHandlerLocator();
        $this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateADataPoolWriter()
    {
        $result = $this->commonFactory->createDomainEventHandlerLocator();
        $this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateADomainEventConsumer()
    {
        $result = $this->commonFactory->createDomainEventConsumer();
        $this->assertInstanceOf(DomainEventConsumer::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnADomainEventQueue()
    {
        $result = $this->commonFactory->getEventQueue();
        $this->assertInstanceOf(Queue::class, $result);
    }

    /**
     * @test
     */
    public function itShouldAlwaysReturnTheSameDomainEventQueueInstance()
    {
        $result1 = $this->commonFactory->getEventQueue();
        $result2 = $this->commonFactory->getEventQueue();
        $this->assertSame($result1, $result2);
    }

    /**
     * @test
     */
    public function itShouldCreateADataPoolReader()
    {
        $result = $this->commonFactory->createDataPoolReader();
        $this->assertInstanceOf(DataPoolReader::class, $result);
    }

    /**
     * @test
     * @expectedException \Brera\UndefinedFactoryMethodException
     * @expectedExceptionMessage Unable to create KeyValueStore. Is the factory registered?
     */
    public function itShouldThrowAnExceptionWithHelpfulMessageIfNoKeyValueStoreFactoryIsRegistered()
    {
        $masterFactory = new PoCMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $commonFactory->createDataPoolReader();
    }

    /**
     * @test
     * @expectedException \Brera\UndefinedFactoryMethodException
     * @expectedExceptionMessage Unable to create EventQueue. Is the factory registered?
     */
    public function itShouldThrowAnExceptionWithHelpfulMessageIfNoEventQueueFactoryIsRegistered()
    {
        $masterFactory = new PoCMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $commonFactory->getEventQueue();
    }

    /**
     * @test
     * @expectedException \Brera\UndefinedFactoryMethodException
     * @expectedExceptionMessage Unable to create Logger. Is the factory registered?
     */
    public function itShouldThrowAnExceptionWithHelpfulMessageIfNoLoggerFactoryIsRegistered()
    {
        $masterFactory = new PoCMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $commonFactory->getLogger();
    }

    /**
     * @test
     */
    public function itShouldReturnTheLoggerInstance()
    {
        $resultA = $this->commonFactory->getLogger();
        $resultB = $this->commonFactory->getLogger();
        $this->assertInstanceOf(Logger::class, $resultA);
        $this->assertSame($resultA, $resultB);
    }

    /**
     * @test
     */
    public function itShouldReturnAResourceNotFoundRouter()
    {
        $result = $this->commonFactory->createResourceNotFoundRouter();
        $this->assertInstanceOf(ResourceNotFoundRouter::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAHttpRouterChain()
    {
        $result = $this->commonFactory->createHttpRouterChain();
        $this->assertInstanceOf(HttpRouterChain::class, $result);
    }
}
