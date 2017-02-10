<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImportCatalogCommandHandler
 * @uses \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses \LizardsAndPumpkins\Import\ImportCatalogCommand
 * @uses \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEvent
 */
class ImportCatalogCommandHandlerTest extends TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var ImportCatalogCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);
        $this->commandHandler = new ImportCatalogCommandHandler($this->mockDomainEventQueue);
    }
    
    public function testImplementsCommandHandlerInterface()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testEmitsCatalogImportWasTriggeredEvent()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(CatalogImportWasTriggeredDomainEvent::class));
        $testCommand = new ImportCatalogCommand(DataVersion::fromVersionString('123'), __FILE__);
        $this->commandHandler->process($testCommand->toMessage());
    }
}
