<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\ShutdownWorkerDomainEventHandler
 * @uses   \LizardsAndPumpkins\Messaging\Event\ShutdownWorkerDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ShutdownWorkerDomainEventHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    public static $shutdownWasCalled = false;

    private function createHandler(Message $message): ShutdownWorkerDomainEventHandler
    {
        return new ShutdownWorkerDomainEventHandler($message, $this->mockDomainEventQueue);
    }

    protected function setUp()
    {
        self::$shutdownWasCalled = false;
        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);
    }

    public function testImplementsDomainEventHandlerInterface()
    {
        $message = (new ShutdownWorkerDomainEvent('*'))->toMessage();
        $this->assertInstanceOf(DomainEventHandler::class, $this->createHandler($message));
    }

    public function testRetriesDomainEventIfMessagePidValueDoesNotMatchWithIncrementedRetryCount()
    {
        $sourceEvent = new ShutdownWorkerDomainEvent(strval(getmypid() - 1), 1);
        $this->mockDomainEventQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (ShutdownWorkerDomainEvent $retryEvent) use ($sourceEvent) {
                $this->assertSame($sourceEvent->getRetryCount() + 1, $retryEvent->getRetryCount());
            });
        $this->createHandler($sourceEvent->toMessage())->process();
    }
    
    public function testRetriesEventUpToMaxRetryBoundary()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add');
        $retry = new ShutdownWorkerDomainEvent(strval(getmypid() - 1), ShutdownWorkerDomainEventHandler::MAX_RETRIES - 1);
        $this->createHandler($retry->toMessage())->process();
    }

    public function testDoesNotRetryCommandIfTheMaxRetryBoundaryIsReached()
    {
        $this->mockDomainEventQueue->expects($this->never())->method('add');
        $retry = new ShutdownWorkerDomainEvent(strval(getmypid() - 1), ShutdownWorkerDomainEventHandler::MAX_RETRIES);
        $this->createHandler($retry->toMessage())->process();
    }

    public function testDoesNotCallExitIfMessagePidDoesNotMatchCurrentPid()
    {
        $event = new ShutdownWorkerDomainEvent(strval(getmypid() - 1));
        $this->createHandler($event->toMessage())->process();
        $this->assertFalse(self::$shutdownWasCalled, "The shutdown() function was unexpectedly called");
    }

    public function testCallsExitIfNumericMessagePidMatchesCurrentPid()
    {
        $event = new ShutdownWorkerDomainEvent((string) getmypid());
        $this->createHandler($event->toMessage())->process();
        $this->assertTrue(self::$shutdownWasCalled, "The shutdown() function was not called");
    }

    public function testCallsExitForWildcardPidInMessage()
    {
        $event = new ShutdownWorkerDomainEvent('*');
        $this->createHandler($event->toMessage())->process();
        $this->assertTrue(self::$shutdownWasCalled, "The shutdown() function was not called");
    }
}

function shutdown(int $exitCode = null)
{
    ShutdownWorkerDomainEventHandlerTest::$shutdownWasCalled = true;
}
