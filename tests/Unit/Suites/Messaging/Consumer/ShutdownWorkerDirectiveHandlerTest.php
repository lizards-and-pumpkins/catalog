<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ConsumerShutdownRequestedLogMessage
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ShutdownWorkerDirectiveHandlerTest extends TestCase
{
    public static $shutdownWasCalled = false;

    /**
     * @var EnqueuesMessageEnvelope|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueue;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    private function createHandler(): ShutdownWorkerDirectiveHandler
    {
        return new ShutdownWorkerDirectiveHandler($this->mockQueue, $this->mockLogger);
    }

    protected function setUp()
    {
        self::$shutdownWasCalled = false;
        $this->mockQueue = $this->createMock(EnqueuesMessageEnvelope::class);
        $this->mockLogger = $this->createMock(Logger::class);
    }

    public function testImplementsCommandAndEventHandlerInterfaces()
    {
        $handler = $this->createHandler();
        $this->assertInstanceOf(CommandHandler::class, $handler);
        $this->assertInstanceOf(DomainEventHandler::class, $handler);
    }

    public function testRetriesCommandIfMessagePidValueDoesNotMatchWithIncrementedRetryCount()
    {
        $sourceDirective = new ShutdownWorkerDirective(strval(getmypid() - 1), 42);
        $this->mockQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (ShutdownWorkerDirective $retryDirective) use ($sourceDirective) {
                $this->assertSame($sourceDirective->getRetryCount() + 1, $retryDirective->getRetryCount());
            });
        $this->createHandler()->process($sourceDirective->toMessage());
    }

    public function testRetriesCommandUpToMaxRetryBoundary()
    {
        $this->mockQueue->expects($this->once())->method('add');
        $retry = new ShutdownWorkerDirective(strval(getmypid() - 1), ShutdownWorkerDirectiveHandler::MAX_RETRIES - 1);
        $this->createHandler()->process($retry->toMessage());
    }

    public function testDoesNotRetryCommandIfTheMaxRetryBoundaryIsReached()
    {
        $this->mockQueue->expects($this->never())->method('add');
        $retry = new ShutdownWorkerDirective(strval(getmypid() - 1), ShutdownWorkerDirectiveHandler::MAX_RETRIES);
        $this->createHandler()->process($retry->toMessage());
    }

    public function testDoesNotCallExitIfMessagePidDoesNotMatchCurrentPid()
    {
        $command = new ShutdownWorkerDirective(strval(getmypid() - 1));
        $this->createHandler()->process($command->toMessage());
        $this->assertFalse(self::$shutdownWasCalled, "The shutdown() function was unexpectedly called");
    }

    public function testCallsExitIfNumericMessagePidMatchesCurrentPid()
    {
        $command = new ShutdownWorkerDirective((string) getmypid());
        $this->createHandler()->process($command->toMessage());
        $this->assertTrue(self::$shutdownWasCalled, "The shutdown() function was not called");
    }

    public function testCallsExitForWildcardPidInMessage()
    {
        $command = new ShutdownWorkerDirective('*');
        $this->createHandler()->process($command->toMessage());
        $this->assertTrue(self::$shutdownWasCalled, "The shutdown() function was not called");
    }

    public function testDoesNotLogNonMatchingShutdownDirectives()
    {
        $this->mockLogger->expects($this->never())->method('log');
        $command = new ShutdownWorkerDirective(strval(getmygid() -1));
        $this->createHandler()->process($command->toMessage());
    }

    public function testLogsMatchingShutdownDirective()
    {
        $this->mockLogger->expects($this->once())->method('log');
        $command = new ShutdownWorkerDirective('*');
        $this->createHandler()->process($command->toMessage());
    }
}

function shutdown()
{
    ShutdownWorkerDirectiveHandlerTest::$shutdownWasCalled = true;
}
