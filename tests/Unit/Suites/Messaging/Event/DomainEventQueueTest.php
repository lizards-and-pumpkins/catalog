<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\Stub\TestDomainEvent;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class DomainEventQueueTest extends TestCase
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

        $this->eventQueue = new DomainEventQueue($this->mockQueue);
        $this->mockDataVersion = $this->createMock(DataVersion::class);
    }

    public function testAddsDomainEventToMessageQueue()
    {
        $this->eventQueue->add(new TestDomainEvent());
        $this->assertAddedMessageCount(1);
    }
}
