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
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageWasUpdatedDomainEvent;
use Brera\Image\ImageWasUpdatedDomainEventHandler;
use Brera\Image\UpdateImageCommand;
use Brera\Image\UpdateImageCommandHandler;
use Brera\Product\FilterNavigationFilterCollection;
use Brera\Product\ProductListingMetaInfoSourceBuilder;
use Brera\Product\ProductListingSourceListBuilder;
use Brera\Product\FilterNavigationBlockRenderer;
use Brera\Product\ProductWasUpdatedDomainEvent;
use Brera\Product\ProductWasUpdatedDomainEventHandler;
use Brera\Product\ProductListingWasUpdatedDomainEvent;
use Brera\Product\ProductListingWasUpdatedDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductSourceBuilder;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use Brera\Product\ProductStockQuantityProjector;
use Brera\Product\ProductStockQuantitySnippetRenderer;
use Brera\Product\ProductStockQuantitySourceBuilder;
use Brera\Product\UpdateMultipleProductStockQuantityCommand;
use Brera\Product\UpdateMultipleProductStockQuantityCommandHandler;
use Brera\Product\UpdateProductCommand;
use Brera\Product\UpdateProductCommandHandler;
use Brera\Product\UpdateProductListingCommand;
use Brera\Product\UpdateProductListingCommandHandler;
use Brera\Product\UpdateProductStockQuantityCommand;
use Brera\Product\UpdateProductStockQuantityCommandHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\CommonFactory
 * @covers \Brera\FactoryTrait
 * @uses   \Brera\DataVersion
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\Image\UpdateImageCommandHandler
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\DataPool\DataPoolWriter
 * @uses   \Brera\DataPool\DataPoolReader
 * @uses   \Brera\Content\ContentBlockSnippetRenderer
 * @uses   \Brera\Content\ContentBlockWasUpdatedDomainEvent
 * @uses   \Brera\Content\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \Brera\Content\ContentBlockProjector
 * @uses   \Brera\Content\UpdateContentBlockCommandHandler
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\ContextSource
 * @uses   \Brera\CommandConsumer
 * @uses   \Brera\CommandHandlerLocator
 * @uses   \Brera\DomainEventConsumer
 * @uses   \Brera\DomainEventHandlerLocator
 * @uses   \Brera\TemplateWasUpdatedDomainEvent
 * @uses   \Brera\TemplateWasUpdatedDomainEventHandler
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Product\DefaultNumberOfProductsPerPageSnippetRenderer
 * @uses   \Brera\Product\FilterNavigationFilterCollection
 * @uses   \Brera\Product\PriceSnippetRenderer
 * @uses   \Brera\Product\ProductBackOrderAvailabilitySnippetRenderer
 * @uses   \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\Product\ProductProjector
 * @uses   \Brera\Product\ProductInSearchAutosuggestionSnippetRenderer
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetRenderer
 * @uses   \Brera\Product\ProductListingTemplateProjector
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetProjector
 * @uses   \Brera\Product\ProductListingMetaInfoSourceBuilder
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEventHandler
 * @uses   \Brera\Product\ProductWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \Brera\Product\ProductSearchAutosuggestionMetaSnippetRenderer
 * @uses   \Brera\Product\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \Brera\Product\ProductSearchAutosuggestionTemplateProjector
 * @uses   \Brera\Product\ProductSearchResultMetaSnippetRenderer
 * @uses   \Brera\Product\ProductSearchDocumentBuilder
 * @uses   \Brera\Product\ProductSourceDetailViewSnippetRenderer
 * @uses   \Brera\Product\ProductStockQuantityProjector
 * @uses   \Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler
 * @uses   \Brera\Product\ProductStockQuantitySnippetRenderer
 * @uses   \Brera\Product\UpdateProductCommandHandler
 * @uses   \Brera\Product\UpdateProductListingCommandHandler
 * @uses   \Brera\Product\UpdateProductStockQuantityCommandHandler
 * @uses   \Brera\Product\UpdateMultipleProductStockQuantityCommandHandler
 * @uses   \Brera\Product\ProductDetailViewBlockRenderer
 * @uses   \Brera\Product\ProductDetailViewInContextSnippetRenderer
 * @uses   \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\SnippetRendererCollection
 * @uses   \Brera\Product\ProductListingSourceListBuilder
 * @uses   \Brera\Product\ProductInListingSnippetRenderer
 * @uses   \Brera\Image\ImageWasUpdatedDomainEventHandler
 * @uses   \Brera\Image\ImageMagickResizeStrategy
 * @uses   \Brera\Image\GdResizeStrategy
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessingStrategySequence
 * @uses   \Brera\LocalFilesystemStorageReader
 * @uses   \Brera\LocalFilesystemStorageWriter
 * @uses   \Brera\TemplateProjectorLocator
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
        /** @var ProductWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ProductWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createProductWasUpdatedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testTemplateWasUpdatedDomainEventHandlerIsReturned()
    {
        /** @var TemplateWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(TemplateWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createTemplateWasUpdatedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testProductListingWasUpdatedDomainEventHandlerIsReturned()
    {
        /** @var ProductListingWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ProductListingWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createProductListingWasUpdatedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ProductListingWasUpdatedDomainEventHandler::class, $result);
    }

    public function testProductProjectorIsReturned()
    {
        $result = $this->commonFactory->createProductProjector();
        $this->assertInstanceOf(ProductProjector::class, $result);
    }

    public function testSnippetListIsReturned()
    {
        $result = $this->commonFactory->createSnippetList();
        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testProductDetailViewSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductDetailViewSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testProductSourceBuilderIsReturned()
    {
        $result = $this->commonFactory->createProductSourceBuilder();
        $this->assertInstanceOf(ProductSourceBuilder::class, $result);
    }

    public function testProductListingMetaInfoSourceBuilderIsReturned()
    {
        $result = $this->commonFactory->createProductListingMetaInfoSourceBuilder();
        $this->assertInstanceOf(ProductListingMetaInfoSourceBuilder::class, $result);
    }

    public function testProductListingSourceListBuilderIsReturned()
    {
        $result = $this->commonFactory->createProductListingSourceListBuilder();
        $this->assertInstanceOf(ProductListingSourceListBuilder::class, $result);
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

    public function testSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductInListingSnippetKeyGenerator();
        $this->assertInstanceOf(GenericSnippetKeyGenerator::class, $result);
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
        $result = $this->commonFactory->createProductStockQuantitySourceBuilder();
        $this->assertInstanceOf(ProductStockQuantitySourceBuilder::class, $result);
    }

    public function testProductStockQuantityProjectorIsReturned()
    {
        $result = $this->commonFactory->createProductStockQuantityProjector();
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

    public function testSnippetKeyGeneratorIsReturnedAsProductStockQuantityRendererSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductStockQuantityRendererSnippetKeyGenerator();
        $this->assertInstanceOf(GenericSnippetKeyGenerator::class, $result);
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

    public function testUpdateProductCommandHandlerIsReturned()
    {
        /** @var UpdateProductCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateProductCommand::class, [], [], '', false);
        $result = $this->commonFactory->createUpdateProductCommandHandler($stubCommand);

        $this->assertInstanceOf(UpdateProductCommandHandler::class, $result);
    }

    public function testUpdateProductListingCommandHandlerIsReturned()
    {
        /** @var UpdateProductListingCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateProductListingCommand::class, [], [], '', false);
        $result = $this->commonFactory->createUpdateProductListingCommandHandler($stubCommand);

        $this->assertInstanceOf(UpdateProductListingCommandHandler::class, $result);
    }

    public function testUpdateImageCommandHandlerIsReturned()
    {
        /** @var UpdateImageCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateImageCommand::class, [], [], '', false);
        $result = $this->commonFactory->createUpdateImageCommandHandler($stubCommand);

        $this->assertInstanceOf(UpdateImageCommandHandler::class, $result);
    }

    public function testContentBlockInProductListingSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createContentBlockInProductListingSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testProductSearchResultMetaSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductSearchResultMetaSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testFilterNavigationBlockRendererIsReturned()
    {
        $result = $this->commonFactory->createFilterNavigationBlockRenderer();
        $this->assertInstanceOf(FilterNavigationBlockRenderer::class, $result);
    }

    public function testImageProcessorCollectionIsReturned()
    {
        $result = $this->commonFactory->getImageProcessorCollection();
        $this->assertInstanceOf(ImageProcessorCollection::class, $result);
    }

    public function testSameInstanceOfImageProcessorCollectionIsReturnedOnConsecutiveCalls()
    {
        $resultA = $this->commonFactory->getImageProcessorCollection();
        $resultB = $this->commonFactory->getImageProcessorCollection();

        $this->assertSame($resultA, $resultB);
    }

    public function testFilterNavigationFilterCollectionIsReturned()
    {
        $result = $this->commonFactory->createFilterNavigationFilterCollection();
        $this->assertInstanceOf(FilterNavigationFilterCollection::class, $result);
    }
}
