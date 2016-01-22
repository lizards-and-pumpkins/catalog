<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\ProductListingCriteria;
use LizardsAndPumpkins\Product\ProductListingCriteriaBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\TestFileFixtureTrait;
use LizardsAndPumpkins\Utils\XPathParser;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogXmlParser
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogListingImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\AddProductListingCommand
 * @uses   \LizardsAndPumpkins\Product\UpdateProductCommand
 * @uses   \LizardsAndPumpkins\Image\AddImageCommand
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 * @uses   \LizardsAndPumpkins\Utils\UuidGenerator
 * @uses   \LizardsAndPumpkins\DataVersion
 */
class CatalogImportTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;
    
    private $sharedFixtureFilePath = __DIR__ . '/../../../../../shared-fixture/catalog.xml';

    /**
     * @var ProductXmlToProductBuilderLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductXmlToProductBuilder;

    /**
     * @var ProductListingCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingCriteriaBuilder;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEventQueue;

    /**
     * @var QueueImportCommands|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueueImportCommands;

    /**
     * @var string
     */
    private $testDirectoryPath;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextSource;

    /**
     * @return ProductXmlToProductBuilderLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductXmlToProductBuilder()
    {
        /** @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject $stubProductBuilder */
        $stubProductBuilder = $this->getMock(ProductBuilder::class);
        $stubProductBuilder->method('getId')->willReturn(ProductId::fromString('dummy'));
        $stubProductBuilder->method('getProductForContext')->willReturn($this->getMock(Product::class));

        $productXmlToProductBuilder = $this->getMock(ProductXmlToProductBuilderLocator::class, [], [], '', false);
        $productXmlToProductBuilder->method('createProductBuilderFromXml')->willReturn($stubProductBuilder);
        return $productXmlToProductBuilder;
    }

    /**
     * @return ProductListingCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductsPerPageForContextBuilder()
    {
        $productListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);
        $productListingCriteria->method('getUrlKey')->willReturn('dummy-url-key');

        $productsPerPageForContextBuilder = $this->getMock(ProductListingCriteriaBuilder::class, [], [], '', false);
        $productsPerPageForContextBuilder->method('createProductListingCriteriaFromXml')
            ->willReturn($productListingCriteria);
        return $productsPerPageForContextBuilder;
    }

    /**
     * @param bool $isAvailableInContext
     */
    private function setProductIsAvailableForContextFixture($isAvailableInContext)
    {
        $stubProductBuilder = $this->stubProductXmlToProductBuilder->createProductBuilderFromXml('');
        $stubProductBuilder->method('isAvailableForContext')->willReturn($isAvailableInContext);
        $stubProductBuilder->getProductForContext($this->getMock(Context::class));
    }

    protected function setUp()
    {
        $this->testDirectoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($this->testDirectoryPath);
        
        $this->mockQueueImportCommands = $this->getMock(QueueImportCommands::class, [], [], '', false);
        
        $this->stubProductXmlToProductBuilder = $this->createMockProductXmlToProductBuilder();
        $this->stubProductListingCriteriaBuilder = $this->createMockProductsPerPageForContextBuilder();
        $this->mockEventQueue = $this->getMock(Queue::class);
        $this->contextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->contextSource->method('getAllAvailableContextsWithVersion')->willReturn(
            [$this->getMock(Context::class)]
        );
        $this->mockLogger = $this->getMock(Logger::class);

        $this->catalogImport = new CatalogImport(
            $this->mockQueueImportCommands,
            $this->stubProductXmlToProductBuilder,
            $this->stubProductListingCriteriaBuilder,
            $this->mockEventQueue,
            $this->contextSource,
            $this->mockLogger
        );
    }

    public function testExceptionIsThrownIfImportFileDoesNotExist()
    {
        $this->setExpectedException(
            CatalogImportFileDoesNotExistException::class,
            'Catalog import file not found'
        );
        $this->catalogImport->importFile('/some-not-existing-file.xml');
    }

    public function testExceptionIsThrownIfImportFileIsNotReadable()
    {
        $this->setExpectedException(
            CatalogImportFileNotReadableException::class,
            'Catalog import file is not readable'
        );

        $importFilePath = $this->testDirectoryPath . '/some-not-readable-file.xml';
        $this->createFixtureFile($importFilePath, '', 0000);

        $this->catalogImport->importFile($importFilePath);
    }

    public function testItAddsCommandsForTheProductToQueue()
    {
        $this->mockQueueImportCommands->expects($this->atLeastOnce())->method('forProduct');
        $this->setProductIsAvailableForContextFixture(true);
        
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }

    public function testItAddsNoProductCommandsToTheQueueIfTheProductDoesNotMatchAGivenContext()
    {
        $this->mockQueueImportCommands->expects($this->never())->method('forProduct');
        $this->setProductIsAvailableForContextFixture(false);
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }

    public function testAddsCommandsForTheProductListingToTheQueue()
    {
        $this->mockQueueImportCommands->expects($this->atLeastOnce())->method('forListing');
        $this->setProductIsAvailableForContextFixture(true);
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }

    public function testItAddsCommandsForTheProductImageToTheQueue()
    {
        $this->mockQueueImportCommands->expects($this->atLeastOnce())->method('forImage');
        $this->setProductIsAvailableForContextFixture(true);
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }

    public function testItAddsNoCommandsForImagesIfTheProductDoesNotMatchAGivenContext()
    {
        $this->mockQueueImportCommands->expects($this->never())->method('forImage');
        $this->setProductIsAvailableForContextFixture(false);
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }

    public function testItAddsACatalogWasImportedDomainEventToTheEventQueue()
    {
        $this->mockEventQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(CatalogWasImportedDomainEvent::class));

        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }

    public function testItLogsExceptionsThrownWhileProcessingListingXml()
    {
        $this->mockLogger->expects($this->atLeastOnce())->method('log')
            ->with($this->isInstanceOf(CatalogListingImportCallbackFailureMessage::class));

        $this->mockQueueImportCommands->method('forListing')->willThrowException(new \Exception('dummy'));
        
        $fullXml = file_get_contents($this->sharedFixtureFilePath);
        $onlyListingXml = (new XPathParser($fullXml))->getXmlNodesRawXmlArrayByXPath('/catalog/listings')[0];
        $fixtureFile = $this->getUniqueTempDir() . '/listings.xml';
        $this->createFixtureFile($fixtureFile, '<catalog>' . $onlyListingXml . '</catalog>');
        $this->catalogImport->importFile($fixtureFile);
    }

    public function testItLogsExceptionsThrownDuringProductImport()
    {
        $this->mockLogger->expects($this->atLeastOnce())->method('log')
            ->with($this->isInstanceOf(ProductImportCallbackFailureMessage::class));
        
        /** @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject $stubProductBuilder */
        $stubProductBuilder = $this->getMock(ProductBuilder::class);
        $stubProductBuilder->method('isAvailableForContext')->willReturn(true);
        $stubProductBuilder->method('getProductForContext')->willThrowException(
            new \Exception('dummy exception')
        );

        $stubProductXmlToProductBuilder = $this->getMock(ProductXmlToProductBuilderLocator::class, [], [], '', false);
        $stubProductXmlToProductBuilder->method('createProductBuilderFromXml')->willReturn($stubProductBuilder);

        $this->catalogImport = new CatalogImport(
            $this->mockQueueImportCommands,
            $stubProductXmlToProductBuilder,
            $this->stubProductListingCriteriaBuilder,
            $this->mockEventQueue,
            $this->contextSource,
            $this->mockLogger
        );
        
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }

    public function testItLogsExceptionsThrownDuringProductImageImport()
    {
        $this->setProductIsAvailableForContextFixture(true);
        $this->mockLogger->expects($this->atLeastOnce())->method('log')
            ->with($this->isInstanceOf(ProductImageImportCallbackFailureMessage::class));

        $this->mockQueueImportCommands->method('forImage')->willThrowException(new \Exception('dummy'));

        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }
}
