<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommandHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEvent
 */
class SetCurrentDataVersionCommandHandlerTest extends TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    private function createCommandHandler(Message $message): SetCurrentDataVersionCommandHandler
    {
        return new SetCurrentDataVersionCommandHandler($message, $this->mockDomainEventQueue);
    }

    private function createMessage(): Message
    {
        $payload = [];
        $metadata = ['data_version' => uniqid('v')];
        
        return Message::withCurrentTime(SetCurrentDataVersionCommand::CODE, $payload, $metadata);
    }

    protected function setUp()
    {
        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class); 
    }

    public function testIsACommandHandler()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->createCommandHandler($this->createMessage()));
    }

    public function testAddsCurrentDataVersionWasSetDomainEventToQueue()
    {
        $sourceMessage = $this->createMessage();
        $handler = $this->createCommandHandler($sourceMessage);

        $this->mockDomainEventQueue->expects($this->once())->method('add')
            ->willReturnCallback(function(CurrentDataVersionWasSetDomainEvent $event) use ($sourceMessage) {
                $this->assertEquals((string) $sourceMessage->getMetadata()['data_version'], $event->getDataVersion());
            });
        
        $handler->process();
    }
}
