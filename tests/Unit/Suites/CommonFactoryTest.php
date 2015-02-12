<?php

namespace Brera;

use Brera\Environment\EnvironmentBuilder;
use Brera\Environment\EnvironmentSourceBuilder;
use Brera\Http\ResourceNotFoundRouter;
use Brera\KeyValue\DataPoolReader;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSourceBuilder;
use Brera\Queue\Queue;
use Brera\SearchEngine\InMemorySearchEngine;
use Psr\Log\LoggerInterface;

/**
 * @covers \Brera\CommonFactory
 * @uses   \Brera\DataVersion
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\KeyValue\DataPoolWriter
 * @uses   \Brera\KeyValue\DataPoolReader
 * @uses   \Brera\Environment\EnvironmentBuilder
 * @uses   \Brera\Environment\EnvironmentSourceBuilder
 * @uses   \Brera\DomainEventConsumer
 * @uses   \Brera\DomainEventHandlerLocator
 * @uses   \Brera\UrlPathKeyGenerator
 * @uses   \Brera\Renderer\BlockSnippetRenderer
 * @uses   \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\Product\ProductProjector
 * @uses   \Brera\Product\ProductSnippetRendererCollection
 * @uses   \Brera\Product\ProductImportDomainEventHandler
 * @uses   \Brera\Product\ProductImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEventHandler
 * @uses   \Brera\Product\ProductSearchIndexer
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
    public function itShouldCreateAnEnvironmentSourceBuilder()
    {
        $result = $this->commonFactory->createEnvironmentSourceBuilder();
        $this->assertInstanceOf(EnvironmentSourceBuilder::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateAnEnvironmentBuilder()
    {
        $result = $this->commonFactory->createEnvironmentBuilder();
        $this->assertInstanceOf(EnvironmentBuilder::class, $result);
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
        $this->assertInstanceOf(LoggerInterface::class, $resultA);
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
    public function itShouldReturnInMemorySearchEngine()
    {
        $this->assertInstanceOf(InMemorySearchEngine::class, $this->commonFactory->getSearchEngine());
    }

    /**
     * @test
     */
    public function itShouldReturnArrayOfStrings()
    {
        $this->assertContainsOnly('string', $this->commonFactory->getListOfAttributesToBePutIntoSearchEngine());
    }
}
