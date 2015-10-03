<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\ProductAttributeContextPartsMismatchException;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\ProductListingCriteria;
use LizardsAndPumpkins\Product\ProductListingCriteriaBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImportFailedMessage
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogXmlParser
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\AddProductListingCommand
 * @uses   \LizardsAndPumpkins\Product\UpdateProductCommand
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 * @uses   \LizardsAndPumpkins\Image\AddImageCommand
 * @uses   \LizardsAndPumpkins\Utils\UuidGenerator
 * @uses   \LizardsAndPumpkins\DataVersion
 */
class CatalogImportTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;
    
    private $invalidProductsFixtureFile = __DIR__ . '/../../../../../shared-fixture/catalog-with-invalid-product.xml';
    
    private $sharedFixtureFilePath = __DIR__ . '/../../../../../shared-fixture/catalog.xml';

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var SimpleProductXmlToSimpleProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductXmlToProductBuilder;

    /**
     * @var ProductListingCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingCriteriaBuilder;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $addToCommandQueueSpy;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEventQueue;
    
    /**
     * @var string
     */
    private $testDirectoryPath;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextSource;

    /**
     * @param string $commandClass
     */
    private function assertCommandWasAddedToQueue($commandClass)
    {
        $numberOfInvocations = array_sum(array_map(function ($invocation) use ($commandClass) {
            /** @var \PHPUnit_Framework_MockObject_Invocation_Object $invocation */
            return intval($commandClass === get_class($invocation->parameters[0]));
        }, $this->addToCommandQueueSpy->getInvocations()));

        $message = sprintf('Failed to assert that %s was added to command queue.', $commandClass);
        $this->assertGreaterThan(0, $numberOfInvocations, $message);
    }

    /**
     * @return SimpleProductXmlToSimpleProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductXmlToProductBuilder()
    {
        /** @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject $stubProductBuilder */
        $stubProductBuilder = $this->getMock(ProductBuilder::class);
        $stubProductBuilder->method('getId')->willReturn(ProductId::fromString('dummy'));
        $stubProductBuilder->method('getProductForContext')->willReturn($this->getMock(Product::class));

        $productXmlToProductBuilder = $this->getMock(SimpleProductXmlToSimpleProductBuilder::class, [], [], '', false);
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

    protected function setUp()
    {
        $this->testDirectoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($this->testDirectoryPath);
        
        $this->mockCommandQueue = $this->getMock(Queue::class);
        $this->addToCommandQueueSpy = $this->any();
        $this->mockCommandQueue->expects($this->addToCommandQueueSpy)->method('add');
        $this->stubProductXmlToProductBuilder = $this->createMockProductXmlToProductBuilder();
        $this->stubProductListingCriteriaBuilder = $this->createMockProductsPerPageForContextBuilder();
        $this->mockEventQueue = $this->getMock(Queue::class);
        $this->contextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->contextSource->method('getAllAvailableContextsWithVersion')->willReturn(
            [$this->getMock(Context::class)]
        );
        $this->logger = $this->getMock(Logger::class);

        $this->catalogImport = new CatalogImport(
            $this->mockCommandQueue,
            $this->stubProductXmlToProductBuilder,
            $this->stubProductListingCriteriaBuilder,
            $this->mockEventQueue,
            $this->contextSource,
            $this->logger
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

    public function testExceptionIsLoggedIfProductBuilderIsInvalid()
    {
        $this->stubProductXmlToProductBuilder->method('createProductBuilderFromXml')
            ->willThrowException(new ProductAttributeContextPartsMismatchException('dummy'));

        $this->logger->expects($this->atLeastOnce())->method('log')
            ->with($this->isInstanceOf(ProductImportFailedMessage::class));

        $this->catalogImport->importFile($this->invalidProductsFixtureFile);
    }

    public function testUpdateProductCommandsAreEmitted()
    {
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
        $this->assertCommandWasAddedToQueue(UpdateProductCommand::class);
    }

    public function testAddProductListingCommandsAreEmitted()
    {
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
        
        $this->assertCommandWasAddedToQueue(AddProductListingCommand::class);
    }

    public function testAddImageCommandsAreEmitted()
    {
        $this->catalogImport->importFile($this->sharedFixtureFilePath);
        
        $this->assertCommandWasAddedToQueue(AddImageCommand::class);
    }

    public function testItAddsACatalogWasImportedDomainEventToTheEventQueue()
    {
        $this->mockEventQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(CatalogWasImportedDomainEvent::class));

        $this->catalogImport->importFile($this->sharedFixtureFilePath);
    }
}
