<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\HttpRouterChain;
use LizardsAndPumpkins\Http\ResourceNotFoundRouter;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\ImageWasUpdatedDomainEvent;
use LizardsAndPumpkins\Image\ImageWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Image\UpdateImageCommand;
use LizardsAndPumpkins\Image\UpdateImageCommandHandler;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\FilterNavigationFilterCollection;
use LizardsAndPumpkins\Product\ProductListingMetaInfoBuilder;
use LizardsAndPumpkins\Product\ProductListingSourceListBuilder;
use LizardsAndPumpkins\Product\FilterNavigationBlockRenderer;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductListingWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductProjector;
use LizardsAndPumpkins\Product\ProductSourceBuilder;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductStockQuantityProjector;
use LizardsAndPumpkins\Product\ProductStockQuantitySnippetRenderer;
use LizardsAndPumpkins\Product\ProductStockQuantitySourceBuilder;
use LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommand;
use LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductListingCommand;
use LizardsAndPumpkins\Product\UpdateProductListingCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductStockQuantityCommand;
use LizardsAndPumpkins\Product\UpdateProductStockQuantityCommandHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport;
use LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\Translator;

/**
 * @covers \LizardsAndPumpkins\CommonFactory
 * @covers \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Image\UpdateImageCommandHandler
 * @uses   \LizardsAndPumpkins\IntegrationTestFactory
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Content\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Content\ContentBlockProjector
 * @uses   \LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\CommandConsumer
 * @uses   \LizardsAndPumpkins\CommandHandlerLocator
 * @uses   \LizardsAndPumpkins\DomainEventConsumer
 * @uses   \LizardsAndPumpkins\DomainEventHandlerLocator
 * @uses   \LizardsAndPumpkins\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 * @uses   \LizardsAndPumpkins\Product\DefaultNumberOfProductsPerPageSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\FilterNavigationFilterCollection
 * @uses   \LizardsAndPumpkins\Product\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductBackOrderAvailabilitySnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSourceBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductProjector
 * @uses   \LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingMetaInfoSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingTemplateProjector
 * @uses   \LizardsAndPumpkins\Product\ProductListingMetaInfoSnippetProjector
 * @uses   \LizardsAndPumpkins\Product\ProductListingMetaInfoBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionTemplateProjector
 * @uses   \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductSourceDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductStockQuantityProjector
 * @uses   \LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Product\ProductStockQuantitySnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\Product\UpdateProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Product\UpdateProductStockQuantityCommandHandler
 * @uses   \LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommandHandler
 * @uses   \LizardsAndPumpkins\Product\ProductDetailViewBlockRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductDetailViewInContextSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\SnippetRendererCollection
 * @uses   \LizardsAndPumpkins\Product\ProductListingSourceListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\Image\ImageWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Image\ImageMagickResizeStrategy
 * @uses   \LizardsAndPumpkins\Image\GdResizeStrategy
 * @uses   \LizardsAndPumpkins\Image\ImageProcessor
 * @uses   \LizardsAndPumpkins\Image\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Image\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\TemplateProjectorLocator
 * @uses   \LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Projection\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\Renderer\ThemeLocator
 * @uses   \LizardsAndPumpkins\Renderer\Translation\CsvTranslator
 * @uses   \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\EnvironmentConfigReader
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

    public function testProductListingMetaInfoBuilderIsReturned()
    {
        $result = $this->commonFactory->createProductListingMetaInfoBuilder();
        $this->assertInstanceOf(ProductListingMetaInfoBuilder::class, $result);
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

    public function testPaginationBlockRendererIsReturned()
    {
        $result = $this->commonFactory->createPaginationBlockRenderer();
        $this->assertInstanceOf(PaginationBlockRenderer::class, $result);
    }

    public function testItReturnsAProcessTimeLoggingDomainEventHandlerDecorator()
    {
        /** @var ProductWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ProductWasUpdatedDomainEvent::class, [], [], '', false);
        $eventHandlerToDecorate = $this->commonFactory->createProductWasUpdatedDomainEventHandler($stubDomainEvent);
        $result = $this->commonFactory->createProcessTimeLoggingDomainEventDecorator($eventHandlerToDecorate);
        $this->assertInstanceOf(ProcessTimeLoggingDomainEventHandlerDecorator::class, $result);
    }

    public function testCatalogImportIsReturned()
    {
        $result = $this->commonFactory->createCatalogImport();
        $this->assertInstanceOf(CatalogImport::class, $result);
    }

    public function testUrlKeyCollectorIsReturned()
    {
        $result = $this->commonFactory->createUrlKeyForContextCollector();
        $this->assertInstanceOf(UrlKeyForContextCollector::class, $result);
    }

    public function testItReturnsTheSameUrlKeyStoreInstance()
    {
        $result1 = $this->commonFactory->getUrlKeyStore();
        $result2 = $this->commonFactory->getUrlKeyStore();
        $this->assertSame($result1, $result2);
    }
    
    public function testTranslatorIsReturned()
    {
        $translatorFactory = $this->commonFactory->getTranslatorFactory();
        $this->assertInstanceOf(Translator::class, $translatorFactory('en_US'));
    }

    public function testItReturnsAConfigReader()
    {
        $result = $this->commonFactory->createConfigReader();
        $this->assertInstanceOf(ConfigReader::class, $result);
    }
}
