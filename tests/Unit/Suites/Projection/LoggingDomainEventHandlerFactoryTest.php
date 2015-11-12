<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\CommonFactory;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\DomainEventHandler;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\IntegrationTestFactory;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\SampleMasterFactory;
use LizardsAndPumpkins\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\TemplateWasUpdatedDomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\Projection\LoggingDomainEventHandlerFactory
 * @uses   \LizardsAndPumpkins\Product\ProductBackOrderAvailabilitySnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductProjector
 * @uses   \LizardsAndPumpkins\Product\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionTemplateProjector
 * @uses   \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductsPerPageForContextListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductListingTemplateProjector
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetProjector
 * @uses   \LizardsAndPumpkins\Product\ProductStockQuantitySnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Product\ProductStockQuantityProjector
 * @uses   \LizardsAndPumpkins\Product\DefaultNumberOfProductsPerPageSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Product\ProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Content\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Content\ContentBlockProjector
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\IntegrationTestFactory
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 * @uses   \LizardsAndPumpkins\Renderer\ThemeLocator
 * @uses   \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\CommonFactory
 * @uses   \LizardsAndPumpkins\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\SnippetRendererCollection
 * @uses   \LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetProjector
 * @uses   \LizardsAndPumpkins\Projection\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\TemplateProjectorLocator
 * @uses   \LizardsAndPumpkins\Image\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\Image\ImageWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Image\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Image\ImageProcessor
 * @uses   \LizardsAndPumpkins\Image\ImageMagickResizeStrategy
 * @uses   \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Utils\LocalFilesystem
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
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new IntegrationTestFactory());
        $this->factory = new LoggingDomainEventHandlerFactory();
        $masterFactory->register($this->factory);
    }

    public function testItReturnsADecoratedProductWasUpdatedDomainEventHandler()
    {
        $stubEvent = $this->getMock(ProductWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createProductWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedTemplateWasUpdatedDomainEventHandler()
    {
        $stubEvent = $this->getMock(TemplateWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createTemplateWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedImageWasAddedDomainEventHandler()
    {
        $stubEvent = $this->getMock(ImageWasAddedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createImageWasAddedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedProductListingWasAddedDomainEventHandler()
    {
        $stubEvent = $this->getMock(ProductListingWasAddedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createProductListingWasAddedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedProductStockQuantityWasUpdatedDomainEventHandler()
    {
        $stubEvent = $this->getMock(ProductStockQuantityWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createProductStockQuantityWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(
            ProductStockQuantityWasUpdatedDomainEventHandler::class,
            $result
        );
    }

    public function testItReturnsADecoratedContentBlockWasUpdatedDomainEventHandler()
    {
        $stubEvent = $this->getMock(ContentBlockWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createContentBlockWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ContentBlockWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedCatalogWasImportedDomainEventHandler()
    {
        $stubEvent = $this->getMock(CatalogWasImportedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createCatalogWasImportedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(CatalogWasImportedDomainEventHandler::class, $result);
    }
}
