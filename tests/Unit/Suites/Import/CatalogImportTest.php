<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductBuilder;
use LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\QueueImportCommands;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

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
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class CatalogImportTest extends TestCase
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
     * @var DataVersion
     */
    private $testDataVersion;

    /**
     * @return ProductXmlToProductBuilderLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductXmlToProductBuilder() : ProductXmlToProductBuilderLocator
    {
        $stubProductBuilder = $this->createMock(ProductBuilder::class);
        $stubProductBuilder->method('getProductForContext')->willReturn($this->createMock(Product::class));

        $productXmlToProductBuilder = $this->createMock(ProductXmlToProductBuilderLocator::class);
        $productXmlToProductBuilder->method('createProductBuilderFromXml')->willReturn($stubProductBuilder);
        return $productXmlToProductBuilder;
    }

    /**
     * @return ProductListingBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductsPerPageForContextBuilder() : ProductListingBuilder
    {
        $productListing = $this->createMock(ProductListing::class);
        $productListing->method('getUrlKey')->willReturn('dummy-url-key');

        $productsPerPageForContextBuilder = $this->createMock(ProductListingBuilder::class);
        $productsPerPageForContextBuilder->method('createProductListingFromXml')->willReturn($productListing);

        return $productsPerPageForContextBuilder;
    }

    private function setProductIsAvailableForContextFixture(bool $isAvailableInContext)
    {
        /** @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject $stubProductBuilder */
        $stubProductBuilder = $this->stubProductXmlToProductBuilder->createProductBuilderFromXml('');
        $stubProductBuilder->method('isAvailableForContext')->willReturn($isAvailableInContext);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);

        $stubProductBuilder->getProductForContext($stubContext);
    }

    protected function setUp()
    {
        $this->testDirectoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($this->testDirectoryPath);

        $this->mockQueueImportCommands = $this->createMock(QueueImportCommands::class);

        $this->stubProductXmlToProductBuilder = $this->createMockProductXmlToProductBuilder();
        $this->stubProductListingBuilder = $this->createMockProductsPerPageForContextBuilder();
        $this->mockEventQueue = $this->createMock(DomainEventQueue::class);
        $this->contextSource = $this->createMock(ContextSource::class);
        $this->contextSource->method('getAllAvailableContextsWithVersionApplied')->willReturn(
            [$this->createMock(Context::class)]
        );
        $this->mockLogger = $this->createMock(Logger::class);
        $this->testDataVersion = DataVersion::fromVersionString('test');

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
        $this->catalogImport->importFile('/some-not-existing-file.xml', $this->testDataVersion);
    }

    public function testExceptionIsThrownIfImportFileIsNotReadable()
    {
        $this->expectException(CatalogImportFileNotReadableException::class);
        $this->expectExceptionMessage('Catalog import file is not readable');

        $importFilePath = $this->testDirectoryPath . '/some-not-readable-file.xml';
        $this->createFixtureFile($importFilePath, '', 0000);

        $this->catalogImport->importFile($importFilePath, $this->testDataVersion);
    }

    public function testItAddsCommandsForTheProductToQueue()
    {
        $this->mockQueueImportCommands->expects($this->atLeastOnce())->method('forProduct');
        $this->setProductIsAvailableForContextFixture(true);
        $this->catalogImport->importFile($this->sharedFixtureFilePath, $this->testDataVersion);
    }

    public function testItAddsCommandsForOneProductToQueue()
    {
        $xml = <<<XML
<product type="simple" sku="288193NEU" tax_class="19%">
    <images>
        <image>
            <file>288193_14.jpg</file>
            <label/>
        </image>
    </images>
    <attributes>
        <attribute name="url_key">adilette</attribute>
        <attribute name="name">Adilette</attribute>
        <attribute name="category">sale</attribute>
        <attribute name="price">14.92</attribute>
        <attribute name="brand">Adidas</attribute>
        <attribute name="gender">Herren</attribute>
        <attribute name="stock_qty">1</attribute>
        <attribute name="backorders">false</attribute>
        <attribute name="color">d90000</attribute>
        <attribute name="series">Adilette</attribute>
        <attribute name="news_from_date">2015-02-25 00:00:00</attribute>
    </attributes>
</product>
XML;

        $this->mockQueueImportCommands->expects($this->atLeastOnce())->method('forProduct');
        $this->setProductIsAvailableForContextFixture(true);
        $this->catalogImport->addProductsAndProductImagesToQueue($xml, $this->testDataVersion);
    }

    public function testItAddsNoProductCommandsToTheQueueIfTheProductDoesNotMatchAGivenContext()
    {
        $this->mockQueueImportCommands->expects($this->never())->method('forProduct');
        $this->setProductIsAvailableForContextFixture(false);
        $this->catalogImport->importFile($this->sharedFixtureFilePath, $this->testDataVersion);
    }

    public function testAddsCommandsForTheProductListingToTheQueue()
    {
        $this->mockQueueImportCommands->expects($this->atLeastOnce())->method('forListing');
        $this->setProductIsAvailableForContextFixture(true);
        $this->catalogImport->importFile($this->sharedFixtureFilePath, $this->testDataVersion);
    }

    public function testItAddsCommandsForTheProductImageToTheQueue()
    {
        $this->mockQueueImportCommands->expects($this->atLeastOnce())->method('forImage');
        $this->setProductIsAvailableForContextFixture(true);
        $this->catalogImport->importFile($this->sharedFixtureFilePath, $this->testDataVersion);
    }

    public function testItAddsNoCommandsForImagesIfTheProductDoesNotMatchAGivenContext()
    {
        $this->mockQueueImportCommands->expects($this->never())->method('forImage');
        $this->setProductIsAvailableForContextFixture(false);
        $this->catalogImport->importFile($this->sharedFixtureFilePath, $this->testDataVersion);
    }

    public function testItAddsACatalogWasImportedDomainEventToTheEventQueue()
    {
        $this->mockEventQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(CatalogWasImportedDomainEvent::class));

        $this->catalogImport->importFile($this->sharedFixtureFilePath, $this->testDataVersion);
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
        $this->catalogImport->importFile($fixtureFile, $this->testDataVersion);
    }

    public function testItLogsExceptionsThrownDuringProductImport()
    {
        $this->mockLogger->expects($this->atLeastOnce())->method('log')
            ->with($this->isInstanceOf(ProductImportCallbackFailureMessage::class));

        /** @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject $stubProductBuilder */
        $stubProductBuilder = $this->createMock(ProductBuilder::class);
        $stubProductBuilder->method('isAvailableForContext')->willReturn(true);
        $stubProductBuilder->method('getProductForContext')->willThrowException(
            new \Exception('dummy exception')
        );

        /** @var ProductXmlToProductBuilderLocator|\PHPUnit_Framework_MockObject_MockObject $stubProductXmlToProductBuilder */
        $stubProductXmlToProductBuilder = $this->createMock(ProductXmlToProductBuilderLocator::class);
        $stubProductXmlToProductBuilder->method('createProductBuilderFromXml')->willReturn($stubProductBuilder);

        $this->catalogImport = new CatalogImport(
            $this->mockQueueImportCommands,
            $stubProductXmlToProductBuilder,
            $this->stubProductListingBuilder,
            $this->mockEventQueue,
            $this->contextSource,
            $this->mockLogger
        );

        $this->catalogImport->importFile($this->sharedFixtureFilePath, $this->testDataVersion);
    }

    public function testItLogsExceptionsThrownDuringProductImageImport()
    {
        $this->setProductIsAvailableForContextFixture(true);
        $this->mockLogger->expects($this->atLeastOnce())->method('log')
            ->with($this->isInstanceOf(ProductImageImportCallbackFailureMessage::class));

        $this->mockQueueImportCommands->method('forImage')->willThrowException(new \Exception('dummy'));

        $this->catalogImport->importFile($this->sharedFixtureFilePath, $this->testDataVersion);
    }
}
