<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommandHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEvent
 */
class SetCurrentDataVersionCommandHandlerTest extends TestCase
{
    /**
     * @var DomainEventQueue|MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var DataPoolWriter|MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var DataPoolReader|MockObject
     */
    private $mockDataPoolReader;

    private function createCommandHandler(): SetCurrentDataVersionCommandHandler
    {
        return new SetCurrentDataVersionCommandHandler(
            $this->mockDomainEventQueue,
            $this->mockDataPoolReader,
            $this->mockDataPoolWriter
        );
    }

    private function createMessage(): Message
    {
        $payload = [];
        $metadata = ['data_version' => uniqid('v')];

        return Message::withCurrentTime(SetCurrentDataVersionCommand::CODE, $payload, $metadata);
    }

    final protected function setUp(): void
    {
        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);
    }

    public function testIsACommandHandler(): void
    {
        $this->assertInstanceOf(CommandHandler::class, $this->createCommandHandler());
    }

    public function testAddsCurrentDataVersionWasSetDomainEventToQueue(): void
    {
        $sourceMessage = $this->createMessage();
        $handler = $this->createCommandHandler();

        $this->mockDomainEventQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (CurrentDataVersionWasSetDomainEvent $event) use ($sourceMessage) {
                $this->assertEquals((string) $sourceMessage->getMetadata()['data_version'], $event->getDataVersion());
            });

        $handler->process($sourceMessage);
    }

    public function testSetsTheDataVersionFromTheCommandViaTheDataPoolWriter(): void
    {
        $sourceMessage = $this->createMessage();
        $dataVersionString = $sourceMessage->getMetadata()['data_version'];
        $this->mockDataPoolWriter->expects($this->once())->method('setCurrentDataVersion')->with($dataVersionString);
        $this->createCommandHandler()->process($sourceMessage);
    }

    public function testSetsThePreviousDataVersionFromTheDataPoolReaderViaTheDataPoolWriter(): void
    {
        $previousVersion = '-1';
        $this->mockDataPoolReader->method('getCurrentDataVersion')->willReturn($previousVersion);
        $this->mockDataPoolWriter->expects($this->once())->method('setPreviousDataVersion')->with($previousVersion);
        $this->createCommandHandler()->process($this->createMessage());
    }
}
