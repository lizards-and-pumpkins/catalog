<?php

namespace Brera;

use Brera\Content\ContentBlockWasUpdatedDomainEvent;
use Brera\Content\ContentBlockWasUpdatedDomainEventHandler;
use Brera\Content\UpdateContentBlockCommand;
use Brera\Content\UpdateContentBlockCommandHandler;
use Brera\Context\ContextBuilder;
use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolReader;
use Brera\Http\HttpRouterChain;
use Brera\Http\ResourceNotFoundRouter;
use Brera\Image\ImageWasUpdatedDomainEvent;
use Brera\Image\ImageWasUpdatedDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductWasUpdatedDomainEvent;
use Brera\Product\ProductWasUpdatedDomainEventHandler;
use Brera\Product\ProductListingWasUpdatedDomainEvent;
use Brera\Product\ProductListingWasUpdatedDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSnippetKeyGenerator;
use Brera\Product\ProductSourceBuilder;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use Brera\Product\ProductStockQuantityProjector;
use Brera\Product\ProductStockQuantitySnippetRenderer;
use Brera\Product\ProductStockQuantitySourceBuilder;
use Brera\Product\UpdateMultipleProductStockQuantityCommand;
use Brera\Product\UpdateMultipleProductStockQuantityCommandHandler;
use Brera\Product\UpdateProductStockQuantityCommand;
use Brera\Product\UpdateProductStockQuantityCommandHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\CommonFactory
 * @covers \Brera\FactoryTrait
 * @uses   \Brera\DataVersion
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\DataPool\DataPoolWriter
 * @uses   \Brera\DataPool\DataPoolReader
 * @uses   \Brera\Content\ContentBlockSnippetRenderer
 * @uses   \Brera\Content\ContentBlockWasUpdatedDomainEvent
 * @uses   \Brera\Content\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \Brera\Content\ContentBlockProjector
 * @uses   \Brera\Content\UpdateContentBlockCommandHandler
 * @uses   \Brera\Content\ContentBlockSnippetKeyGenerator
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
 * @uses   \Brera\Product\ProductBackOrderAvailabilitySnippetRenderer
 * @uses   \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\Product\ProductProjector
 * @uses   \Brera\Product\ProductSnippetKeyGenerator
 * @uses   \Brera\Product\ProductListingCriteriaSnippetRenderer
 * @uses   \Brera\Product\ProductListingProjector
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEventHandler
 * @uses   \Brera\Product\ProductWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \Brera\Product\CatalogImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEventHandler
 * @uses   \Brera\Product\ProductSearchDocumentBuilder
 * @uses   \Brera\Product\ProductSourceDetailViewSnippetRenderer
 * @uses   \Brera\Product\ProductStockQuantityProjector
 * @uses   \Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler
 * @uses   \Brera\Product\ProductStockQuantitySnippetRenderer
 * @uses   \Brera\Product\UpdateProductStockQuantityCommandHandler
 * @uses   \Brera\Product\UpdateMultipleProductStockQuantityCommandHandler
 * @uses   \Brera\Product\ProductDetailViewBlockRenderer
 * @uses   \Brera\Product\ProductDetailViewInContextSnippetRenderer
 * @uses   \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\SnippetRendererCollection
 * @uses   \Brera\RootSnippetSourceListBuilder
 * @uses   \Brera\Product\ProductSourceInListingSnippetRenderer
 * @uses   \Brera\Product\ProductInListingInContextSnippetRenderer
 * @uses   \Brera\Image\ImageWasUpdatedDomainEventHandler
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
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new IntegrationTestFactory());
        $this->commonFactory = new CommonFactory();
        $masterFactory->register($this->commonFactory);
    }

    public function testExceptionIsThrownIfNoMasterFactoryIsSet()
    {
        $this->setExpectedException(NoMasterFactorySetException::class);
        (new CommonFactory())->createDomainEventConsumer();
    }

    public function testProductWasUpdatedDomainEventHandlerIsReturned()
    {
        /* TODO: Move to catalog factory test */
        /** @var ProductWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ProductWasUpdatedDomainEvent::class, [], [], '', false);;
        $result = $this->commonFactory->createProductWasUpdatedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testCatalogImportDomainEventHandlerIsReturned()
    {
        /* TODO: Move to catalog factory test */
        /** @var CatalogImportDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(CatalogImportDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createCatalogImportDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(CatalogImportDomainEventHandler::class, $result);
    }

    public function testRootTemplateChangedDomainEventHandlerIsReturned()
    {
        /* TODO: Move to catalog factory test */
        /** @var RootTemplateChangedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(RootTemplateChangedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createRootTemplateChangedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(RootTemplateChangedDomainEventHandler::class, $result);
    }

    public function testProductListingWasUpdatedDomainEventHandlerIsReturned()
    {
        /* TODO: Move to catalog factory test */
        /** @var ProductListingWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ProductListingWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createProductListingWasUpdatedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ProductListingWasUpdatedDomainEventHandler::class, $result);
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
        $masterFactory = new SampleMasterFactory();
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
        $masterFactory = new SampleMasterFactory();
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
        $masterFactory = new SampleMasterFactory();
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
        /* @var ImageWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ImageWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createImageWasUpdatedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ImageWasUpdatedDomainEventHandler::class, $result);
    }

    public function testProductSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductInListingSnippetKeyGenerator();
        $this->assertInstanceOf(ProductSnippetKeyGenerator::class, $result);
    }

    public function testUpdateProductStockQuantityCommandHandlerIsReturned()
    {
        /** @var UpdateProductStockQuantityCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateProductStockQuantityCommand::class, [], [], '', false);
        $result = $this->commonFactory->createUpdateProductStockQuantityCommandHandler($stubCommand);

        $this->assertInstanceOf(UpdateProductStockQuantityCommandHandler::class, $result);
    }

    public function testUpdateMultipleProductStockQuantityCommandHandlerIsReturned()
    {
        /** @var UpdateMultipleProductStockQuantityCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateMultipleProductStockQuantityCommand::class, [], [], '', false);
        $result = $this->commonFactory->createUpdateMultipleProductStockQuantityCommandHandler($stubCommand);

        $this->assertInstanceOf(UpdatemultipleProductStockQuantityCommandHandler::class, $result);
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

    public function testProductStockQuantityWasUpdatedDomainEventHandlerIsReturned()
    {
        /** @var ProductStockQuantityWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ProductStockQuantityWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createProductStockQuantityWasUpdatedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ProductStockQuantityWasUpdatedDomainEventHandler::class, $result);
    }

    public function testUpdateContentBlockCommandHandlerIsReturned()
    {
        /** @var UpdateContentBlockCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateContentBlockCommand::class, [], [], '', false);
        $result = $this->commonFactory->createUpdateContentBlockCommandHandler($stubCommand);

        $this->assertInstanceOf(UpdateContentBlockCommandHandler::class, $result);
    }

    public function testContentBlockWasUpdatedDomainEventHandlerIsReturned()
    {
        /** @var ContentBlockWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ContentBlockWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createContentBlockWasUpdatedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ContentBlockWasUpdatedDomainEventHandler::class, $result);
    }
}
