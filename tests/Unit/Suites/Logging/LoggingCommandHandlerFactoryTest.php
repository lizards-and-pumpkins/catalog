<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Import\Image\AddImageCommand;
use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\Product\UpdateProductCommand;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective;
use LizardsAndPumpkins\ProductListing\AddProductListingCommand;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;
use LizardsAndPumpkins\UnitTestFactory;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommand
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommand
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @uses   \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommand
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommandHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommandHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 */
class LoggingCommandHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggingCommandHandlerFactory
     */
    private $loggingCommandHandlerFactory;

    protected function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);
        $masterFactory->register(new UnitTestFactory($this));
        $this->loggingCommandHandlerFactory = new LoggingCommandHandlerFactory($commonFactory);
        $masterFactory->register($this->loggingCommandHandlerFactory);
    }

    public function testItImplementsTheCommandFactoryInterfaceAndFactoryInterface()
    {
        $this->assertInstanceOf(CommandHandlerFactory::class, $this->loggingCommandHandlerFactory);
        $this->assertInstanceOf(Factory::class, $this->loggingCommandHandlerFactory);
    }

    public function testItReturnsADecoratedUpdateContentBlockCommandHandler()
    {
        $contentBlockSource = new ContentBlockSource(ContentBlockId::fromString('qux'), '', [], []);
        $message = (new UpdateContentBlockCommand($contentBlockSource))->toMessage();
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateContentBlockCommandHandler($message);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedUpdateProductCommandHandler()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([DataVersion::CONTEXT_CODE => '123']);
        $stubContext->method('getValue')->willReturn('123');
        $product = new SimpleProduct(
            new ProductId('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            $stubContext
        );
        $message = (new UpdateProductCommand($product))->toMessage();
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateProductCommandHandler($message);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedAddProductListingCommandHandler()
    {
        /** @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('serialize')->willReturn(serialize($stubProductListing));
        $message = (new AddProductListingCommand($stubProductListing))->toMessage();
        $commandHandler = $this->loggingCommandHandlerFactory->createAddProductListingCommandHandler($message);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedAddProductImageCommandHandler()
    {
        $message = (new AddImageCommand(__FILE__, DataVersion::fromVersionString('buz')))->toMessage();
        $commandHandler = $this->loggingCommandHandlerFactory->createAddImageCommandHandler($message);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testReturnsADecoratedShutdownWorkerDirectiveHandler()
    {
        $message = (new ShutdownWorkerDirective('4321'))->toMessage();
        $commandHandler = $this->loggingCommandHandlerFactory->createShutdownWorkerCommandHandler($message);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testReturnsADecoratedImportCatalogCommandHandler()
    {
        $message = (new ImportCatalogCommand(DataVersion::fromVersionString('buz'), __FILE__))->toMessage();
        $commandHandler = $this->loggingCommandHandlerFactory->createImportCatalogCommandHandler($message);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testReturnsADecoratedSetCurrentDataVersionCommandHandler()
    {
        $message = (new SetCurrentDataVersionCommand(DataVersion::fromVersionString('qux')))->toMessage();
        $commandHandler = $this->loggingCommandHandlerFactory->createSetCurrentDataVersionCommandHandler($message);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }
}
