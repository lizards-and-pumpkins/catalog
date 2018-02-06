<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class CommandQueueTest extends TestCase
{
    /**
     * @var CommandQueue
     */
    private $commandQueue;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueue;

    /**
     * @var AnyInvokedCount
     */
    private $addToQueueSpy;

    /**
     * @return Message[]
     */
    private function getMessagesAddedToQueue() : array
    {
        return array_map(function (ObjectInvocation $invocation) {
            return $invocation->getParameters()[0];
        }, $this->addToQueueSpy->getInvocations());
    }

    private function assertAddedMessageCount(int $expected)
    {
        $queueMessages = $this->getMessagesAddedToQueue();
        $message = sprintf('Expected queue message count to be %d, got %d', $expected, count($queueMessages));
        $this->assertCount($expected, $queueMessages, $message);
    }

    protected function setUp()
    {
        $this->mockQueue = $this->createMock(Queue::class);
        $this->addToQueueSpy = new AnyInvokedCount();
        $this->mockQueue->expects($this->addToQueueSpy)->method('add');

        $this->commandQueue = new CommandQueue($this->mockQueue);
    }

    public function testAddsCommandsToQueue()
    {
        /** @var Command|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->createMock(Command::class);
        $command->method('toMessage')->willReturn($this->createMock(Message::class));
        $this->commandQueue->add($command);
        $this->assertAddedMessageCount(1);
    }
}
