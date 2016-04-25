<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
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
 * @uses   \LizardsAndPumpkins\Context\Website\ContextWebsite
 * @uses   \LizardsAndPumpkins\Context\Country\ContextCountry
 * @uses   \LizardsAndPumpkins\Context\Locale\ContextLocale
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\InMemory\InMemorySearchEngine
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
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ImageMagickResizeStrategy
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
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
        /** @var ProductWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(ProductWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createProductWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedTemplateWasUpdatedDomainEventHandler()
    {
        /** @var TemplateWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(TemplateWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createTemplateWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedImageWasAddedDomainEventHandler()
    {
        /** @var ImageWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(ImageWasAddedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createImageWasAddedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedProductListingWasAddedDomainEventHandler()
    {
        /** @var ProductListingWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(ProductListingWasAddedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createProductListingWasAddedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedContentBlockWasUpdatedDomainEventHandler()
    {
        /** @var ContentBlockWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(ContentBlockWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createContentBlockWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ContentBlockWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedCatalogWasImportedDomainEventHandler()
    {
        /** @var CatalogWasImportedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(CatalogWasImportedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createCatalogWasImportedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(CatalogWasImportedDomainEventHandler::class, $result);
    }
}
