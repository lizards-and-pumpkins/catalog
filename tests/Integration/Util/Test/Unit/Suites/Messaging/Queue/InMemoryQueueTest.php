<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Util\Storage\Clearable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\InMemoryQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class InMemoryQueueTest extends TestCase
{
    /**
     * @var InMemoryQueue
     */
    private $queue;

    /**
     * @var MessageReceiver|MockObject
     */
    private $mockMessageReceiver;

    /**
     * @var Message
     */
    private $testMessage;

    final protected function setUp(): void
    {
        $this->testMessage = Message::withCurrentTime('foo', [], []);
        $this->mockMessageReceiver = $this->createMock(MessageReceiver::class);
        $this->queue = new InMemoryQueue();
    }

    public function testIsInitiallyEmpty(): void
    {
        $this->assertCount(0, $this->queue);
    }

    public function testCallsMessageReceiverWithMessage(): void
    {
        $this->queue->add($this->testMessage);
        $this->mockMessageReceiver->expects($this->once())->method('receive')->with($this->testMessage);
        $this->queue->consume($this->mockMessageReceiver, $numberOfMessagesBeforeReturn = 1);
    }

    public function testRemovesConsumedMessageFromQueue(): void
    {
        $this->queue->add($this->testMessage);
        $this->queue->consume($this->mockMessageReceiver, $numberOfMessagesBeforeReturn = 1);

        $this->assertCount(0, $this->queue);
    }

    public function testReturnsTheMessagesInTheRightOrder(): void
    {
        $this->queue->add(Message::withCurrentTime('One', [], []));
        $this->queue->add(Message::withCurrentTime('Two', [], []));

        $this->mockMessageReceiver->expects($this->exactly(2))->method('receive')->withConsecutive(
            [
                $this->callback(function (Message $message) {
                    return $message->getName() === 'One';
                }),
            ],
            [
                $this->callback(function (Message $message) {
                    return $message->getName() === 'Two';
                })
            ]
        );
        $this->queue->consume($this->mockMessageReceiver, $numberOfMessagesBeforeReturn = 2);
    }

    public function testIsClearable(): void
    {
        $this->assertInstanceOf(Clearable::class, $this->queue);
    }

    public function testClearsTheQueue(): void
    {
        $this->queue->add(Message::withCurrentTime('One', [], []));
        $this->queue->add(Message::withCurrentTime('Two', [], []));
        $this->queue->add(Message::withCurrentTime('Three', [], []));
        $this->assertCount(3, $this->queue);
        $this->queue->clear();
        $this->assertCount(0, $this->queue);
    }
}
