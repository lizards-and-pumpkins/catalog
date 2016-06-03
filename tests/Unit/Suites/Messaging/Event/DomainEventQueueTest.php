<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\Stub\TestDomainEvent;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class DomainEventQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataVersion;

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
     * @return Message
     */
    private function getAddedMessage()
    {
        $messages = $this->getMessagesAddedToQueue();
        if (count($messages) === 0) {
            $this->fail('No messages added to queue');
        }
        return $messages[0];
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

        $this->eventQueue = new DomainEventQueue($this->mockQueue);
        $this->mockDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
    }

    public function testAddsDomainEventToMessageQueue()
    {
        $this->eventQueue->addVersioned(new TestDomainEvent(), $this->mockDataVersion);
        $this->assertAddedMessageCount(1);
    }
    
    public function testCreatesVersionedQueueMessage()
    {
        $this->eventQueue->addVersioned(new TestDomainEvent(), $this->mockDataVersion);

        $message = $this->getAddedMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertArrayHasKey(DomainEventQueue::VERSION_KEY, $message->getMetadata());
        $this->assertSame((string)$this->mockDataVersion, $message->getMetadata()[DomainEventQueue::VERSION_KEY]);
    }

    public function testCreatesUnVersionedQueueMessage()
    {
        $this->eventQueue->addNotVersioned(new TestDomainEvent());
        
        $message = $this->getAddedMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertArrayNotHasKey(DomainEventQueue::VERSION_KEY, $message->getMetadata());
    }
}
