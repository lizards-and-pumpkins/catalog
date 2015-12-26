<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextCountry;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\Context\ContextBuilder\ContextPartBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextVersion;
use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\Exception\NoMasterFactorySetException;
use LizardsAndPumpkins\Exception\UndefinedFactoryMethodException;
use LizardsAndPumpkins\Http\HttpRouterChain;
use LizardsAndPumpkins\Http\ResourceNotFoundRouter;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingCriteriaBuilder;
use LizardsAndPumpkins\Product\ProductSearch\ConfigurableProductAttributeValueCollector;
use LizardsAndPumpkins\Product\ProductSearch\DefaultAttributeValueCollector;
use LizardsAndPumpkins\Product\ProductSearch\AttributeValueCollectorLocator;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductProjector;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\AddProductListingCommandHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetProjector;
use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
use LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\Translator;
use LizardsAndPumpkins\Utils\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Utils\ImageStorage\MediaBaseUrlBuilder;
use LizardsAndPumpkins\Website\ConfigurableHostToWebsiteMap;
use LizardsAndPumpkins\Website\HostToWebsiteMap;

/**
 * @covers \LizardsAndPumpkins\CommonFactory
 * @covers \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Content\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Content\ContentBlockProjector
 * @uses   \LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextLocale
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextCountry
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\CommandConsumer
 * @uses   \LizardsAndPumpkins\CommandHandlerLocator
 * @uses   \LizardsAndPumpkins\DomainEventConsumer
 * @uses   \LizardsAndPumpkins\DomainEventHandlerLocator
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductProjector
 * @uses   \LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingTemplateProjector
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetProjector
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionTemplateProjector
 * @uses   \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearch\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductSearch\AttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\Product\ProductSearch\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Product\ProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\Product\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Product\ProductDetailViewBlockRenderer
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\SnippetRendererCollection
 * @uses   \LizardsAndPumpkins\Product\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\Image\ImageWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Image\ImageMagickResizeStrategy
 * @uses   \LizardsAndPumpkins\Image\GdResizeStrategy
 * @uses   \LizardsAndPumpkins\Image\ImageProcessor
 * @uses   \LizardsAndPumpkins\Image\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Image\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetProjector
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Projection\TemplateProjectorLocator
 * @uses   \LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Projection\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\Renderer\ThemeLocator
 * @uses   \LizardsAndPumpkins\Renderer\Translation\CsvTranslator
 * @uses   \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\SnippetList
 * @uses   \LizardsAndPumpkins\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Utils\LocalFilesystem
 * @uses   \LizardsAndPumpkins\Website\ConfigurableHostToWebsiteMap
 * @uses   \LizardsAndPumpkins\Utils\FileStorage\FilesystemFileStorage
 * @uses   \LizardsAndPumpkins\Utils\ImageStorage\MediaDirectoryBaseUrlBuilder
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
        $masterFactory->register(new UnitTestFactory());
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

    public function testProductListingWasAddedDomainEventHandlerIsReturned()
    {
        /** @var ProductListingWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ProductListingWasAddedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createProductListingWasAddedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
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

    public function testProductXmlToProductBuilderLocatorIsReturned()
    {
        $result = $this->commonFactory->createProductXmlToProductBuilderLocator();
        $this->assertInstanceOf(ProductXmlToProductBuilderLocator::class, $result);
    }

    public function testProductXmlToProductBuilderLocatorProxyFactoryIsReturned()
    {
        $proxy = $this->commonFactory->createProductXmlToProductBuilderLocatorProxyFactoryMethod();
        $this->assertInstanceOf(ProductXmlToProductBuilderLocator::class, $proxy());
    }

    public function testProductListingCriteriaBuilderIsReturned()
    {
        $result = $this->commonFactory->createProductListingCriteriaBuilder();
        $this->assertInstanceOf(ProductListingCriteriaBuilder::class, $result);
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
        /* @var ImageWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(ImageWasAddedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createImageWasAddedDomainEventHandler($stubDomainEvent);

        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductInListingSnippetKeyGenerator();
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

    public function testAddProductListingCommandHandlerIsReturned()
    {
        /** @var AddProductListingCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(AddProductListingCommand::class, [], [], '', false);
        $result = $this->commonFactory->createAddProductListingCommandHandler($stubCommand);

        $this->assertInstanceOf(AddProductListingCommandHandler::class, $result);
    }

    public function testAddImageCommandHandlerIsReturned()
    {
        /** @var AddImageCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(AddImageCommand::class, [], [], '', false);
        $result = $this->commonFactory->createAddImageCommandHandler($stubCommand);

        $this->assertInstanceOf(AddImageCommandHandler::class, $result);
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

    public function testItReturnsACatalogWasImportedDomainEventHandler()
    {
        /** @var CatalogWasImportedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(CatalogWasImportedDomainEvent::class, [], [], '', false);
        $result = $this->commonFactory->createCatalogWasImportedDomainEventHandler($stubEvent);
        $this->assertInstanceOf(CatalogWasImportedDomainEventHandler::class, $result);
    }

    public function testItReturnsAProductListingPageSnippetProjector()
    {
        $result = $this->commonFactory->createProductListingPageSnippetProjector();
        $this->assertInstanceOf(ProductListingPageSnippetProjector::class, $result);
    }

    public function testItReturnsAProductJsonSnippetRenderer()
    {
        $result = $this->commonFactory->createProductJsonSnippetRenderer();
        $this->assertInstanceOf(ProductJsonSnippetRenderer::class, $result);
    }

    public function testItReturnsAProductJsonSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsAConfigurableProductJsonSnippetRenderer()
    {
        $result = $this->commonFactory->createConfigurableProductJsonSnippetRenderer();
        $this->assertInstanceOf(ConfigurableProductJsonSnippetRenderer::class, $result);
    }

    public function testItReturnsAConfigurableProductVariationAttributesJsonSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsAConfigurableProductAssociatedProductsJsonSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsAnInternalToPublicProductJsonData()
    {
        $result = $this->commonFactory->createInternalToPublicProductJsonData();
        $this->assertInstanceOf(InternalToPublicProductJsonData::class, $result);
    }

    public function testSearchCriteriaBuilderIsReturned()
    {
        $result = $this->commonFactory->createSearchCriteriaBuilder();
        $this->assertInstanceOf(SearchCriteriaBuilder::class, $result);
    }

    public function testSnippetKeyGeneratorForContentBlockIsReturned()
    {
        $snippetCode = 'content_block_foo';
        $snippetKeyGeneratorLocator = $this->commonFactory->createContentBlockSnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testSnippetKeyGeneratorForProductListingContentBlockIsReturned()
    {
        $snippetCode = 'product_listing_content_block_foo';
        $snippetKeyGeneratorLocator = $this->commonFactory->createContentBlockSnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsABaseUrlBuilder()
    {
        $result = $this->commonFactory->createBaseUrlBuilder();
        $this->assertInstanceOf(BaseUrlBuilder::class, $result);
    }

    public function testItReturnsAVersionContextPartBuilder()
    {
        $result = $this->commonFactory->createVersionContextPartBuilder();
        $this->assertInstanceOf(ContextPartBuilder::class, $result);
        $this->assertInstanceOf(ContextVersion::class, $result);
    }

    public function testItReturnsAWebsiteContextPartBuilder()
    {
        $result = $this->commonFactory->createWebsiteContextPartBuilder();
        $this->assertInstanceOf(ContextPartBuilder::class, $result);
        $this->assertInstanceOf(ContextWebsite::class, $result);
    }

    public function testItReturnsALocaleContextPartBuilder()
    {
        $result = $this->commonFactory->createLocaleContextPartBuilder();
        $this->assertInstanceOf(ContextPartBuilder::class, $result);
        $this->assertInstanceOf(ContextLocale::class, $result);
    }

    public function testItReturnsAHostToWebsiteMap()
    {
        $result = $this->commonFactory->createHostToWebsiteMap();
        $this->assertInstanceOf(HostToWebsiteMap::class, $result);
        $this->assertInstanceOf(ConfigurableHostToWebsiteMap::class, $result);
    }

    public function testItReturnsACountryContextPartBuilder()
    {
        $result = $this->commonFactory->createCountryContextPartBuilder();
        $this->assertInstanceOf(ContextPartBuilder::class, $result);
        $this->assertInstanceOf(ContextCountry::class, $result);
    }

    public function testItReturnsAFilesystemFileStorage()
    {
        $this->assertInstanceOf(FilesystemFileStorage::class, $this->commonFactory->createFilesystemFileStorage());
    }

    public function testItReturnsTheMediaBaseDirectoryConfiguration()
    {
        $baseDirectory = $this->commonFactory->getMediaBaseDirectoryConfig();
        $this->assertInternalType('string', $baseDirectory);
    }

    public function testItReturnsAMediaDirectoryBaseUrlBuilderinstance()
    {
        $result = $this->commonFactory->createMediaBaseUrlBuilder();
        $this->assertInstanceOf(MediaBaseUrlBuilder::class, $result);
    }

    public function testItReturnsAnAttributeValueCollectorLocator()
    {
        $result = $this->commonFactory->createAttributeValueCollectorLocator();
        $this->assertInstanceOf(AttributeValueCollectorLocator::class, $result);
    }

    public function testItReturnsADefaultAttributeValueCollector()
    {
        $result = $this->commonFactory->createDefaultAttributeValueCollector();
        $this->assertInstanceOf(DefaultAttributeValueCollector::class, $result);
    }

    public function testItReturnsAConfigurableProductAttributeValueCollector()
    {
        $result = $this->commonFactory->createConfigurableProductAttributeValueCollector();
        $this->assertInstanceOf(ConfigurableProductAttributeValueCollector::class, $result);
    }
}
