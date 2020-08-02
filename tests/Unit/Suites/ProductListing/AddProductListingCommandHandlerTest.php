<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommand
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class AddProductListingCommandHandlerTest extends TestCase
{
    /**
     * @var DomainEventQueue|MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var AddProductListingCommandHandler
     */
    private $commandHandler;

    private function createTestMessage(): Message
    {
        /** @var ProductListing|MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('getContextData')->willReturn([DataVersion::CONTEXT_CODE => '123']);
        $stubProductListing->method('serialize')->willReturn(serialize($stubProductListing));

        return (new AddProductListingCommand($stubProductListing))->toMessage();
    }
    
    final protected function setUp(): void
    {
        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);

        $this->commandHandler = new AddProductListingCommandHandler($this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testProductListingWasAddedDomainEventIsEmitted(): void
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add');

        $this->commandHandler->process($this->createTestMessage());
    }
}
