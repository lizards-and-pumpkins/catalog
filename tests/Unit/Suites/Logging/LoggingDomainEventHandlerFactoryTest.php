<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingDomainEventHandlerFactory
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductProjector
 * @uses   \LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Price\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductSearch\ProductInSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionTemplateProjector
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Import\SnippetRendererCollection
 * @uses   \LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 */
class LoggingDomainEventHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggingDomainEventHandlerFactory
     */
    private $factory;

    /**
     * @param string $expectedClassName
     * @param DomainEventHandler $actual
     */
    private function assertDecoratedDomainEventHandlerInstanceOf($expectedClassName, DomainEventHandler $actual)
    {
        $this->assertInstanceOf(ProcessTimeLoggingDomainEventHandlerDecorator::class, $actual);
        $this->assertAttributeInstanceOf($expectedClassName, 'component', $actual);
    }

    protected function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);
        $masterFactory->register(new UnitTestFactory());
        $this->factory = new LoggingDomainEventHandlerFactory($commonFactory);
        $masterFactory->register($this->factory);
    }

    public function testItReturnsADecoratedProductWasUpdatedDomainEventHandler()
    {
        $testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContext::fromArray([DataVersion::CONTEXT_CODE => 'buz'])
        );
        $testEvent = new ProductWasUpdatedDomainEvent($testProduct);
        $result = $this->factory->createProductWasUpdatedDomainEventHandler($testEvent->toMessage());
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedTemplateWasUpdatedDomainEventHandler()
    {
        $testEvent = new TemplateWasUpdatedDomainEvent('foo', 'bar');
        $result = $this->factory->createTemplateWasUpdatedDomainEventHandler($testEvent->toMessage());
        $this->assertDecoratedDomainEventHandlerInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedImageWasAddedDomainEventHandler()
    {
        $testEvent = new ImageWasAddedDomainEvent('foo', DataVersion::fromVersionString('foo'));
        $result = $this->factory->createImageWasAddedDomainEventHandler($testEvent->toMessage());
        $this->assertDecoratedDomainEventHandlerInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedProductListingWasAddedDomainEventHandler()
    {
        /** @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing */
        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $stubProductListing->method('getContextData')->willReturn([DataVersion::CONTEXT_CODE => 'foo']);
        $stubProductListing->method('serialize')->willReturn(serialize($stubProductListing));
        $testEvent = new ProductListingWasAddedDomainEvent($stubProductListing);
        $result = $this->factory->createProductListingWasAddedDomainEventHandler($testEvent->toMessage());
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedContentBlockWasUpdatedDomainEventHandler()
    {
        $testContentBlockSource = new ContentBlockSource(
            ContentBlockId::fromString('foo'),
            '',
            [],
            []
        );
        $testEvent = new ContentBlockWasUpdatedDomainEvent($testContentBlockSource);
        $result = $this->factory->createContentBlockWasUpdatedDomainEventHandler($testEvent->toMessage());
        $this->assertDecoratedDomainEventHandlerInstanceOf(ContentBlockWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedCatalogWasImportedDomainEventHandler()
    {
        $testEvent = new CatalogWasImportedDomainEvent(DataVersion::fromVersionString('foo'));
        $result = $this->factory->createCatalogWasImportedDomainEventHandler($testEvent->toMessage());
        $this->assertDecoratedDomainEventHandlerInstanceOf(CatalogWasImportedDomainEventHandler::class, $result);
    }
}
