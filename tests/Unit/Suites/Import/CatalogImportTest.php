<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductBuilder;
use LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\QueueImportCommands;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Import\XmlParser\CatalogXmlParser
 * @uses   \LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Import\CatalogListingImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 * @uses   \LizardsAndPumpkins\Util\UuidGenerator
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class CatalogImportTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;
    
    private $sharedFixtureFilePath = __DIR__ . '/../../../shared-fixture/catalog.xml';

    /**
     * @var ProductXmlToProductBuilderLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductXmlToProductBuilder;

    /**
     * @var ProductListingBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingBuilder;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
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
        $stubProductBuilder->method('getProductForContext')->willReturn($this->getMock(Product::class));

        $productXmlToProductBuilder = $this->getMock(ProductXmlToProductBuilderLocator::class, [], [], '', false);
        $productXmlToProductBuilder->method('createProductBuilderFromXml')->willReturn($stubProductBuilder);
        return $productXmlToProductBuilder;
    }

    /**
     * @return ProductListingBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductsPerPageForContextBuilder()
    {
        $productListing = $this->getMock(ProductListing::class, [], [], '', false);
        $productListing->method('getUrlKey')->willReturn('dummy-url-key');

        $productsPerPageForContextBuilder = $this->getMock(ProductListingBuilder::class, [], [], '', false);
        $productsPerPageForContextBuilder->method('createProductListingFromXml')->willReturn($productListing);

        return $productsPerPageForContextBuilder;
    }

    /**
     * @param bool $isAvailableInContext
     */
    private function setProductIsAvailableForContextFixture($isAvailableInContext)
    {
        $stubProductBuilder = $this->stubProductXmlToProductBuilder->createProductBuilderFromXml('');
        $stubProductBuilder->method('isAvailableForContext')->willReturn($isAvailableInContext);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $stubProductBuilder->getProductForContext($stubContext);
    }

    protected function setUp()
    {
        $this->testDirectoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($this->testDirectoryPath);
        
        $this->mockQueueImportCommands = $this->getMock(QueueImportCommands::class, [], [], '', false);
        
        $this->stubProductXmlToProductBuilder = $this->createMockProductXmlToProductBuilder();
        $this->stubProductListingBuilder = $this->createMockProductsPerPageForContextBuilder();
        $this->mockEventQueue = $this->getMock(DomainEventQueue::class, [], [], '', false);
        $this->contextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->contextSource->method('getAllAvailableContextsWithVersion')->willReturn(
            [$this->getMock(Context::class)]
        );
        $this->mockLogger = $this->getMock(Logger::class);

        $this->catalogImport = new CatalogImport(
            $this->mockQueueImportCommands,
            $this->stubProductXmlToProductBuilder,
            $this->stubProductListingBuilder,
            $this->mockEventQueue,
            $this->contextSource,
            $this->mockLogger
        );
    }

    public function testExceptionIsThrownIfImportFileDoesNotExist()
    {
        $this->expectException(CatalogImportFileDoesNotExistException::class);
        $this->expectExceptionMessage('Catalog import file not found');
        $this->catalogImport->importFile('/some-not-existing-file.xml');
    }

    public function testExceptionIsThrownIfImportFileIsNotReadable()
    {
        $this->expectException(CatalogImportFileNotReadableException::class);
        $this->expectExceptionMessage('Catalog import file is not readable');

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
        $this->mockEventQueue->expects($this->once())->method('addVersioned')
            ->with('catalog_was_imported', $this->anything(), $this->anything());

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
            $this->stubProductListingBuilder,
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
