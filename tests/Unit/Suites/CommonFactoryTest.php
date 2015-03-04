<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Context\ContextSourceBuilder;
use Brera\Http\ResourceNotFoundRouter;
use Brera\Http\HttpRouterChain;
use Brera\DataPool\DataPoolReader;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
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
 * @uses   \Brera\Context\ContextSourceBuilder
 * @uses   \Brera\DomainEventConsumer
 * @uses   \Brera\DomainEventHandlerLocator
 * @uses   \Brera\RootSnippetChangedDomainEvent
 * @uses   \Brera\RootSnippetChangedDomainEventHandler
 * @uses   \Brera\RootSnippetProjector
 * @uses   \Brera\UrlPathKeyGenerator
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\Product\ProductProjector
 * @uses   \Brera\Product\ProductSnippetRendererCollection
 * @uses   \Brera\Product\ProductImportDomainEvent
 * @uses   \Brera\Product\ProductImportDomainEventHandler
 * @uses   \Brera\Product\CatalogImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEventHandler
 * @uses   \Brera\Product\ProductSearchDocumentBuilder
 * @uses   \Brera\Product\ProductInContextDetailViewSnippetRenderer
 * @uses   \Brera\Product\ProductSourceDetailViewSnippetRenderer
 * @uses   \Brera\Product\ProductDetailViewBlockRenderer
 * @uses   \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\RootSnippetRendererCollection
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
        (new CommonFactory())->createContextSourceBuilder();
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateAProductImportDomainEventHandler()
    {
        $productImportDomainEvent = new ProductImportDomainEvent('<xml></xml>');
        $result = $this->commonFactory->createProductImportDomainEventHandler($productImportDomainEvent);
        $this->assertInstanceOf(ProductImportDomainEventHandler::class, $result);
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateACatalogImportDomainEventHandler()
    {
        $catalogImportDomainEvent = new CatalogImportDomainEvent('<xml></xml>');
        $result = $this->commonFactory->createCatalogImportDomainEventHandler($catalogImportDomainEvent);
        $this->assertInstanceOf(CatalogImportDomainEventHandler::class, $result);
    }

    /**
     * @test
     * @todo Move to catalog factory test
     */
    public function itShouldCreateARootSnippetChangedDomainEventHandler()
    {
        $rootSnippetChangedDomainEvent = new RootSnippetChangedDomainEvent('<xml></xml>');
        $result = $this->commonFactory->createRootSnippetChangedDomainEventHandler($rootSnippetChangedDomainEvent);
        $this->assertInstanceOf(RootSnippetChangedDomainEventHandler::class, $result);
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
    public function itShouldCreateAnContextSourceBuilder()
    {
        $result = $this->commonFactory->createContextSourceBuilder();
        $this->assertInstanceOf(ContextSourceBuilder::class, $result);
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

    /**
     * @test
     */
    public function itShouldAlwaysReturnTheSameKeyGenratorLocatorViaGetter()
    {
        $result1 = $this->commonFactory->getSnippetKeyGeneratorLocator();
        $result2 = $this->commonFactory->getSnippetKeyGeneratorLocator();
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $result1);
        $this->assertSame($result1, $result2);
    }
}
