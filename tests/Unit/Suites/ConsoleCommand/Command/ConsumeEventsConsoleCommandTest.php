<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use LizardsAndPumpkins\ConsoleCommand\ConsoleCommand;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ConsumeEventsConsoleCommand
 */
class ConsumeEventsConsoleCommandTest extends TestCase
{
    /**
     * @var MasterFactory
     */
    private $stubMasterFactory;

    final protected function setUp(): void
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->onlyMethods(get_class_methods(MasterFactory::class))
            ->addMethods(['createDomainEventConsumer'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testIsAConsoleCommand(): void
    {
        $command = new ConsumeEventsConsoleCommand($this->stubMasterFactory);
        $this->assertInstanceOf(ConsoleCommand::class, $command);
    }

    public function testRegistersUpdatingCommandFactories(): void
    {
        $this->stubMasterFactory->expects($this->at(0))->method('register')
            ->with($this->isInstanceOf(UpdatingProductImportCommandFactory::class));
        $this->stubMasterFactory->expects($this->at(1))->method('register')
            ->with($this->isInstanceOf(UpdatingProductImageImportCommandFactory::class));
        $this->stubMasterFactory->expects($this->at(2))->method('register')
            ->with($this->isInstanceOf(UpdatingProductListingImportCommandFactory::class));

        new ConsumeEventsConsoleCommand($this->stubMasterFactory);
    }

    public function testCallsProcessOnDomainEventConsumer(): void
    {
        $mockDomainEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockDomainEventConsumer->expects($this->once())->method('process');
        $this->stubMasterFactory->method('createDomainEventConsumer')->willReturn($mockDomainEventConsumer);
        
        (new ConsumeEventsConsoleCommand($this->stubMasterFactory))->run();
    }
}
