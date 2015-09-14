<?php


namespace Brera\Projection;

use Brera\CommonFactory;
use Brera\Content\ContentBlockWasUpdatedDomainEvent;
use Brera\Content\ContentBlockWasUpdatedDomainEventHandler;
use Brera\DomainEventHandler;
use Brera\Image\ImageWasUpdatedDomainEvent;
use Brera\Image\ImageWasUpdatedDomainEventHandler;
use Brera\IntegrationTestFactory;
use Brera\Product\ProductListingWasUpdatedDomainEvent;
use Brera\Product\ProductListingWasUpdatedDomainEventHandler;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use Brera\Product\ProductWasUpdatedDomainEvent;
use Brera\Product\ProductWasUpdatedDomainEventHandler;
use Brera\SampleMasterFactory;
use Brera\TemplateWasUpdatedDomainEvent;
use Brera\TemplateWasUpdatedDomainEventHandler;

/**
 * @covers \Brera\Projection\LoggingDomainEventHandlerFactory
 * @uses   \Brera\Product\ProductBackOrderAvailabilitySnippetRenderer
 * @uses   \Brera\Product\ProductProjector
 * @uses   \Brera\Product\ProductInListingSnippetRenderer
 * @uses   \Brera\Product\ProductSearchDocumentBuilder
 * @uses   \Brera\Product\ProductDetailViewInContextSnippetRenderer
 * @uses   \Brera\Product\PriceSnippetRenderer
 * @uses   \Brera\Product\ProductSourceDetailViewSnippetRenderer
 * @uses   \Brera\Product\ProductInSearchAutosuggestionSnippetRenderer
 * @uses   \Brera\Product\ProductSearchAutosuggestionMetaSnippetRenderer
 * @uses   \Brera\Product\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \Brera\Product\ProductSearchAutosuggestionTemplateProjector
 * @uses   \Brera\Product\ProductSearchResultMetaSnippetRenderer
 * @uses   \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetRenderer
 * @uses   \Brera\Product\ProductListingSourceListBuilder
 * @uses   \Brera\Product\ProductListingTemplateProjector
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetProjector
 * @uses   \Brera\Product\ProductStockQuantitySnippetRenderer
 * @uses   \Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler
 * @uses   \Brera\Product\ProductStockQuantityProjector
 * @uses   \Brera\Product\DefaultNumberOfProductsPerPageSnippetRenderer
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEventHandler
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\ContextSource
 * @uses   \Brera\Content\ContentBlockSnippetRenderer
 * @uses   \Brera\Content\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \Brera\Content\ContentBlockProjector
 * @uses   \Brera\DataPool\DataPoolWriter
 * @uses   \Brera\DataPool\DataPoolReader
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \Brera\DataVersion
 * @uses   \Brera\CommonFactory
 * @uses   \Brera\TemplateWasUpdatedDomainEventHandler
 * @uses   \Brera\LocalFilesystemStorageReader
 * @uses   \Brera\LocalFilesystemStorageWriter
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\SnippetRendererCollection
 * @uses   \Brera\Projection\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \Brera\TemplateProjectorLocator
 * @uses   \Brera\Image\ImageProcessingStrategySequence
 * @uses   \Brera\Image\ImageWasUpdatedDomainEventHandler
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageMagickResizeStrategy
 * @uses   \Brera\Renderer\Translation\TranslatorRegistry
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

    public function testItReturnsADecoratedImageWasUpdatedDomainEventHandler()
    {
        $stubEvent = $this->getMock(ImageWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createImageWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ImageWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedProductListingWasUpdatedDomainEventHandler()
    {
        $stubEvent = $this->getMock(ProductListingWasUpdatedDomainEvent::class, [], [], '', false);
        $result = $this->factory->createProductListingWasUpdatedDomainEventHandler($stubEvent);
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductListingWasUpdatedDomainEventHandler::class, $result);
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
}
