<?php

namespace Brera;

use Brera\Environment\EnvironmentBuilder;
use Brera\Environment\EnvironmentSourceBuilder;
use Brera\KeyValue\DataPoolReader;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSourceBuilder;
use Brera\Queue\Queue;

/**
 * @covers \Brera\CommonFactory
 * @uses   \Brera\KeyValue\DataPoolWriter
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 * @uses   \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Environment\EnvironmentBuilder
 * @uses   \Brera\DomainEventHandlerLocator
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
}
