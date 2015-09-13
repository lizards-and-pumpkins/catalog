<?php


namespace Brera\Projection\Catalog\Import;

use Brera\Image\UpdateImageCommand;
use Brera\Log\Logger;
use Brera\Product\Exception\ProductAttributeContextPartsMismatchException;
use Brera\Product\ProductId;
use Brera\Product\ProductSource;
use Brera\Product\UpdateProductCommand;
use Brera\Product\UpdateProductListingCommand;
use Brera\Product\ProductListingMetaInfoSource;
use Brera\Product\ProductListingMetaInfoSourceBuilder;
use Brera\Product\ProductSourceBuilder;
use Brera\Queue\Queue;
use org\bovigo\vfs\vfsStream;

/**
 * @covers \Brera\Projection\Catalog\Import\CatalogImport
 * @uses \Brera\Product\ProductId
 * @uses \Brera\Product\UpdateProductListingCommand
 * @uses \Brera\Product\UpdateProductCommand
 * @uses \Brera\Projection\Catalog\Import\ProductImportFailedMessage
 * @uses \Brera\Utils\XPathParser
 * @uses \Brera\Projection\Catalog\Import\CatalogXmlParser
 * @uses \Brera\Image\UpdateImageCommand
 */
class CatalogImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var ProductSourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductSourceBuilder;

    /**
     * @var ProductListingMetaInfoSourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingMetaInfoSourceBuilder;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $vfsRoot;

    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $addToCommandQueueSpy;

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
     * @return ProductSourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductSourceBuilder()
    {
        $productSource = $this->getMock(ProductSource::class, [], [], '', false);
        $productSource->method('getId')->willReturn(ProductId::fromString('dummy'));
        
        $productSourceBuilder = $this->getMock(ProductSourceBuilder::class, [], [], '', false);
        $productSourceBuilder->method('createProductSourceFromXml')->willReturn($productSource);
        return $productSourceBuilder;
    }

    /**
     * @return ProductListingMetaInfoSourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductListingSourceBuilder()
    {
        $productListingMetaInfoSource = $this->getMock(ProductListingMetaInfoSource::class, [], [], '', false);
        $productListingMetaInfoSource->method('getUrlKey')->willReturn('dummy-url-key');

        $productListingSourceBuilder = $this->getMock(ProductListingMetaInfoSourceBuilder::class, [], [], '', false);
        $productListingSourceBuilder->method('createProductListingMetaInfoSourceFromXml')
            ->willReturn($productListingMetaInfoSource);
        return $productListingSourceBuilder;
    }

    protected function setUp()
    {
        $this->mockCommandQueue = $this->getMock(Queue::class);
        $this->addToCommandQueueSpy = $this->any();
        $this->mockCommandQueue->expects($this->addToCommandQueueSpy)->method('add');
        $this->stubProductSourceBuilder = $this->createMockProductSourceBuilder();
        $this->stubProductListingMetaInfoSourceBuilder = $this->createMockProductListingSourceBuilder();
        $this->logger = $this->getMock(Logger::class);
        $this->vfsRoot = vfsStream::setup('root');

        $this->catalogImport = new CatalogImport(
            $this->mockCommandQueue,
            $this->stubProductSourceBuilder,
            $this->stubProductListingMetaInfoSourceBuilder,
            $this->logger
        );
    }

    public function testExceptionIsThrownIfImportFileDoesNotExist()
    {
        $this->setExpectedException(
            Exception\CatalogImportFileDoesNotExistException::class,
            'Catalog import file not found'
        );
        $this->catalogImport->importFile(vfsStream::url('root/some-not-existing-file.xml'));
    }

    public function testExceptionIsThrownIfImportFileIsNotReadable()
    {
        $this->setExpectedException(
            Exception\CatalogImportFileNotReadableException::class,
            'Catalog import file is not readable'
        );

        $importFilePath = vfsStream::url('root/some-not-readable-file.xml');
        touch($importFilePath);
        chmod($importFilePath, 0000);

        $this->catalogImport->importFile($importFilePath);
    }

    public function testExceptionIsLoggedIfProductSourceIsInvalid()
    {
        $importFilePath = vfsStream::url('root/catalog-import.xml');
        $fixtureFile = __DIR__ . '/../../../../../shared-fixture/catalog-with-invalid-product.xml';
        file_put_contents($importFilePath, file_get_contents($fixtureFile));

        $this->stubProductSourceBuilder->method('createProductSourceFromXml')
            ->willThrowException(new ProductAttributeContextPartsMismatchException('dummy'));

        $this->logger->expects($this->atLeastOnce())->method('log')
            ->with($this->isInstanceOf(ProductImportFailedMessage::class));

        $this->catalogImport->importFile($importFilePath);
    }

    public function testUpdateProductCommandsAreEmitted()
    {
        $importFilePath = vfsStream::url('root/catalog-import.xml');
        $fixtureFile = __DIR__ . '/../../../../../shared-fixture/catalog.xml';
        file_put_contents($importFilePath, file_get_contents($fixtureFile));

        $this->catalogImport->importFile($importFilePath);
        $this->assertCommandWasAddedToQueue(UpdateProductCommand::class);
    }

    public function testUpdateProductListingCommandsAreEmitted()
    {
        $importFilePath = vfsStream::url('root/catalog-import.xml');
        $fixtureFile = __DIR__ . '/../../../../../shared-fixture/catalog.xml';
        file_put_contents($importFilePath, file_get_contents($fixtureFile));

        $this->catalogImport->importFile($importFilePath);
        
        $this->assertCommandWasAddedToQueue(UpdateProductListingCommand::class);
    }

    public function testUpdateImageCommandsAreEmitted()
    {
        $importFilePath = vfsStream::url('root/catalog-import.xml');
        $fixtureFile = __DIR__ . '/../../../../../shared-fixture/catalog.xml';
        file_put_contents($importFilePath, file_get_contents($fixtureFile));

        $this->catalogImport->importFile($importFilePath);
        
        $this->assertCommandWasAddedToQueue(UpdateImageCommand::class);
    }
}
