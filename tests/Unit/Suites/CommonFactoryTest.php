<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolReader;
use Brera\Http\HttpRouterChain;
use Brera\Http\ResourceNotFoundRouter;
use Brera\Image\ImageImportDomainEvent;
use Brera\Image\ImageImportDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSavedDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSnippetKeyGenerator;
use Brera\Product\ProductSourceBuilder;
use Brera\Product\ProductStockQuantityChangedDomainEvent;
use Brera\Product\ProductStockQuantityChangedDomainEventHandler;
use Brera\Product\ProductStockQuantityProjector;
use Brera\Product\ProductStockQuantitySnippetRenderer;
use Brera\Product\ProductStockQuantitySourceBuilder;
use Brera\Product\ProjectProductStockQuantitySnippetCommand;
use Brera\Product\ProjectProductStockQuantitySnippetCommandHandler;
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
 * @uses   \Brera\CommandConsumer
 * @uses   \Brera\CommandHandlerLocator
 * @uses   \Brera\DomainEventConsumer
 * @uses   \Brera\DomainEventHandlerLocator
 * @uses   \Brera\RootTemplateChangedDomainEvent
 * @uses   \Brera\RootTemplateChangedDomainEventHandler
 * @uses   \Brera\RootSnippetProjector
 * @uses   \Brera\UrlPathKeyGenerator
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Product\PriceSnippetRenderer
 * @uses   \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\Product\ProductProjector
 * @uses   \Brera\Product\ProductSnippetKeyGenerator
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
 * @uses   \Brera\Product\ProductStockQuantityProjector
 * @uses   \Brera\Product\ProductStockQuantityChangedDomainEventHandler
 * @uses   \Brera\Product\ProductStockQuantitySnippetRenderer
 * @uses   \Brera\Product\ProjectProductStockQuantitySnippetCommandHandler
 * @uses   \Brera\Product\ProductDetailViewBlockRenderer
 * @uses   \Brera\Product\ProductDetailViewInContextSnippetRenderer
 * @uses   \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\SnippetRendererCollection
 * @uses   \Brera\RootSnippetSourceListBuilder
 * @uses   \Brera\Product\ProductSourceInListingSnippetRenderer
 * @uses   \Brera\Product\ProductInListingInContextSnippetRenderer
 * @uses   \Brera\Image\ImageImportDomainEventHandler
 * @uses   \Brera\Image\ImageMagickResizeStrategy
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessingStrategySequence
 * @uses   \Brera\LocalFilesystemStorageReader
 * @uses   \Brera\LocalFilesystemStorageWriter
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

    public function testExceptionIsThrownIfNoMasterFactoryIsSet()
    {
        $this->setExpectedException(NoMasterFactorySetException::class);
        (new CommonFactory())->createDomainEventConsumer();
    }

    public function testProductImportDomainEventHandlerIsReturned()
    {
        /* TODO: Move to catalog factory test */
        $productImportDomainEvent = new ProductImportDomainEvent('<xml/>');
        $result = $this->commonFactory->createProductImportDomainEventHandler($productImportDomainEvent);
        $this->assertInstanceOf(ProductImportDomainEventHandler::class, $result);
    }

    public function testCatalogImportDomainEventHandlerIsReturned()
    {
        /* TODO: Move to catalog factory test */
        $catalogImportDomainEvent = new CatalogImportDomainEvent('<xml/>');
        $result = $this->commonFactory->createCatalogImportDomainEventHandler($catalogImportDomainEvent);
        $this->assertInstanceOf(CatalogImportDomainEventHandler::class, $result);
    }

    public function testRootTemplateChangedDomainEventHandlerIsReturned()
    {
        /* TODO: Move to catalog factory test */
        $rootTemplateChangedDomainEvent = new RootTemplateChangedDomainEvent('<xml/>');
        $result = $this->commonFactory->createRootTemplateChangedDomainEventHandler($rootTemplateChangedDomainEvent);
        $this->assertInstanceOf(RootTemplateChangedDomainEventHandler::class, $result);
    }

    public function testProductListingSavedDomainEventHandlerIsReturned()
    {
        /* TODO: Move to catalog factory test */
        $productListingSavedDomainEvent = new ProductListingSavedDomainEvent('<xml/>');
        $result = $this->commonFactory->createProductListingSavedDomainEventHandler($productListingSavedDomainEvent);
        $this->assertInstanceOf(ProductListingSavedDomainEventHandler::class, $result);
    }

    public function testProductProjectorIsReturned()
    {
        /* TODO: Move to catalog factory test */
        $result = $this->commonFactory->createProductProjector();
        $this->assertInstanceOf(ProductProjector::class, $result);
    }

    public function testUrlPathKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createUrlPathKeyGenerator();
        $this->assertInstanceOf(UrlPathKeyGenerator::class, $result);
    }
    
    public function testSnippetListIsReturned()
    {
        $result = $this->commonFactory->createSnippetList();
        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testProductDetailViewSnippetKeyGeneratorIsReturned()
    {
        /* TODO: Move to catalog factory test */
        $result = $this->commonFactory->createProductDetailViewSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testProductBuilderIsReturned()
    {
        /* TODO: Move to catalog factory test */
        $result = $this->commonFactory->createProductSourceBuilder();
        $this->assertInstanceOf(ProductSourceBuilder::class, $result);
    }

    public function testThemeLocatorIsReturned()
    {
        $result = $this->commonFactory->createThemeLocator();
        $this->assertInstanceOf(ThemeLocator::class, $result);
    }

    public function testContextSourceIsReturned()
    {
        $result = $this->commonFactory->createContextSource();
        $this->assertInstanceOf(ContextSource::class, $result);
    }

    public function testContextBuilderIsReturned()
    {
        $result = $this->commonFactory->createContextBuilder();
        $this->assertInstanceOf(ContextBuilder::class, $result);
    }

    public function testDomainEventHandlerLocatorIsReturned()
    {
        $result = $this->commonFactory->createDomainEventHandlerLocator();
        $this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
    }

    public function testDataPoolWriterIsReturned()
    {
        $result = $this->commonFactory->createDomainEventHandlerLocator();
        $this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
    }

    public function testDomainEventConsumerIsReturned()
    {
        $result = $this->commonFactory->createDomainEventConsumer();
        $this->assertInstanceOf(DomainEventConsumer::class, $result);
    }

    public function testDomainEventQueueIsReturned()
    {
        $result = $this->commonFactory->getEventQueue();
        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testSameDomainEventQueueInstanceIsReturned()
    {
        $result1 = $this->commonFactory->getEventQueue();
        $result2 = $this->commonFactory->getEventQueue();
        $this->assertSame($result1, $result2);
    }

    public function testDataPoolReaderIsReturned()
    {
        $result = $this->commonFactory->createDataPoolReader();
        $this->assertInstanceOf(DataPoolReader::class, $result);
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoKeyValueStoreFactoryIsRegistered()
    {
        $masterFactory = new PoCMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->setExpectedException(
            UndefinedFactoryMethodException::class,
            'Unable to create KeyValueStore. Is the factory registered?'
        );

        $commonFactory->createDataPoolReader();
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoEventQueueFactoryIsRegistered()
    {
        $masterFactory = new PoCMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->setExpectedException(
            UndefinedFactoryMethodException::class,
            'Unable to create EventQueue. Is the factory registered?'
        );

        $commonFactory->getEventQueue();
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoLoggerFactoryIsRegistered()
    {
        $masterFactory = new PoCMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->setExpectedException(
            UndefinedFactoryMethodException::class,
            'Unable to create Logger. Is the factory registered?'
        );

        $commonFactory->getLogger();
    }

    public function testLoggerInstanceIsReturned()
    {
        $resultA = $this->commonFactory->getLogger();
        $resultB = $this->commonFactory->getLogger();
        $this->assertInstanceOf(Logger::class, $resultA);
        $this->assertSame($resultA, $resultB);
    }

    public function testResourceNotFoundRouterIsReturned()
    {
        $result = $this->commonFactory->createResourceNotFoundRouter();
        $this->assertInstanceOf(ResourceNotFoundRouter::class, $result);
    }

    public function testHttpRouterChainIsReturned()
    {
        $result = $this->commonFactory->createHttpRouterChain();
        $this->assertInstanceOf(HttpRouterChain::class, $result);
    }

    public function testImageImportEventDomainHandlerIsReturned()
    {
        /* @var $event \PHPUnit_Framework_MockObject_MockObject|ImageImportDomainEvent */
        $event = $this->getMock(ImageImportDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createImageImportDomainEventHandler($event);

        $this->assertInstanceOf(ImageImportDomainEventHandler::class, $result);
    }

    public function testProductSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductInListingSnippetKeyGenerator();
        $this->assertInstanceOf(ProductSnippetKeyGenerator::class, $result);
    }

    public function testProjectProductStockQuantitySnippetCommandHandlerIsReturned()
    {
        /** @var ProjectProductStockQuantitySnippetCommand $command */
        $command = $this->getMock(ProjectProductStockQuantitySnippetCommand::class, [], [], '', false);
        $result = $this->commonFactory->createProjectProductStockQuantitySnippetCommandHandler($command);

        $this->assertInstanceOf(ProjectProductStockQuantitySnippetCommandHandler::class, $result);
    }

    public function testProductStockQuantitySourceBuilderIsReturned()
    {
        $result = $this->commonFactory->getProductStockQuantitySourceBuilder();
        $this->assertInstanceOf(ProductStockQuantitySourceBuilder::class, $result);
    }

    public function testProductStockQuantityProjectorIsReturned()
    {
        $result = $this->commonFactory->getProductStockQuantityProjector();
        $this->assertInstanceOf(ProductStockQuantityProjector::class, $result);
    }

    public function testSnippetRendererCollectionIsReturned()
    {
        $result = $this->commonFactory->createProductStockQuantitySnippetRendererCollection();
        $this->assertInstanceOf(SnippetRendererCollection::class, $result);
    }

    public function testArrayOfSnippetRenderersIsReturned()
    {
        $result = $this->commonFactory->createProductStockQuantitySnippetRendererList();
        $this->assertContainsOnly(SnippetRenderer::class, $result);
    }

    public function testProductStockQuantitySnippetRendererIsReturned()
    {
        $result = $this->commonFactory->createProductStockQuantitySnippetRenderer();
        $this->assertInstanceOf(ProductStockQuantitySnippetRenderer::class, $result);
    }

    public function testProductSnippetKeyGeneratorIsReturnedAsProductStockQuantityRendererSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductStockQuantityRendererSnippetKeyGenerator();
        $this->assertInstanceOf(ProductSnippetKeyGenerator::class, $result);
    }

    public function testCommandConsumerIsReturned()
    {
        $result = $this->commonFactory->createCommandConsumer();
        $this->assertInstanceOf(CommandConsumer::class, $result);
    }

    public function testCommandQueueIsReturned()
    {
        $result = $this->commonFactory->getCommandQueue();
        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testSameCommandQueueInstanceIsReturned()
    {
        $result1 = $this->commonFactory->getCommandQueue();
        $result2 = $this->commonFactory->getCommandQueue();

        $this->assertSame($result1, $result2);
    }

    public function testCommandHandlerLocatorIsReturned()
    {
        $result = $this->commonFactory->createCommandHandlerLocator();
        $this->assertInstanceOf(CommandHandlerLocator::class, $result);
    }

    public function testProductStockQuantityChangedDomainEventHandlerIsReturned()
    {
        /** @var ProductStockQuantityChangedDomainEvent $event */
        $event = $this->getMock(ProductStockQuantityChangedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createProductStockQuantityChangedDomainEventHandler($event);

        $this->assertInstanceOf(ProductStockQuantityChangedDomainEventHandler::class, $result);
    }
}
