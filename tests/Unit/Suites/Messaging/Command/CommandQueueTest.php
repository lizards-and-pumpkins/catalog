<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class CommandQueueTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $addToQueueSpy;

    /**
     * @return Message[]
     */
    private function getMessagesAddedToQueue()
    {
        return array_map(function (\PHPUnit_Framework_MockObject_Invocation_Static $invocation) {
            return $invocation->parameters[0];
        }, $this->addToQueueSpy->getInvocations());
    }

    /**
     * @param int $expected
     */
    private function assertAddedMessageCount($expected)
    {
        $queueMessages = $this->getMessagesAddedToQueue();
        $message = sprintf('Expected queue message count to be %d, got %d', $expected, count($queueMessages));
        $this->assertCount($expected, $queueMessages, $message);
    }

    protected function setUp()
    {
        $this->mockQueue = $this->getMock(Queue::class);
        $this->addToQueueSpy = $this->any();
        $this->mockQueue->expects($this->addToQueueSpy)->method('add');

        $this->commandQueue = new CommandQueue($this->mockQueue);
    }

    public function testAddsCommandsToQueue()
    {
        /** @var Command|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->getMock(Command::class);
        $command->method('toMessage')->willReturn($this->getMock(Message::class, [], [], '', false));
        $this->commandQueue->add($command);
        $this->assertAddedMessageCount(1);
    }
}
