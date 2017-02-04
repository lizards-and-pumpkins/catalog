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
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    private function createCommandHandler(Message $message): SetCurrentDataVersionCommandHandler
    {
        return new SetCurrentDataVersionCommandHandler(
            $message,
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

    protected function setUp()
    {
        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);
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
            ->willReturnCallback(function (CurrentDataVersionWasSetDomainEvent $event) use ($sourceMessage) {
                $this->assertEquals((string) $sourceMessage->getMetadata()['data_version'], $event->getDataVersion());
            });

        $handler->process();
    }

    public function testSetsTheDataVersionFromTheCommandViaTheDataPoolWriter()
    {
        $sourceMessage = $this->createMessage();
        $dataVersionString = $sourceMessage->getMetadata()['data_version'];
        $this->mockDataPoolWriter->expects($this->once())->method('setCurrentDataVersion')->with($dataVersionString);
        $this->createCommandHandler($sourceMessage)->process();
    }

    public function testSetsThePreviousDataVersionFromTheDataPoolReaderViaTheDataPoolWriter()
    {
        $previousVersion = '-1';
        $this->mockDataPoolReader->method('getCurrentDataVersion')->willReturn($previousVersion);
        $this->mockDataPoolWriter->expects($this->once())->method('setPreviousDataVersion')->with($previousVersion);
        $this->createCommandHandler($this->createMessage())->process();
    }
}
