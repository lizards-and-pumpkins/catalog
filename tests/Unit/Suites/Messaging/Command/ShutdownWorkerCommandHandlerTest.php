<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Command;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\ShutdownWorkerCommandHandler
 * @uses   \LizardsAndPumpkins\Messaging\Command\ShutdownWorkerCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ShutdownWorkerCommandHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CommandQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;
    
    public static $shutdownWasCalled = false;

    private function createHandler($message): ShutdownWorkerCommandHandler
    {
        return new ShutdownWorkerCommandHandler($message, $this->mockCommandQueue);
    }

    protected function setUp()
    {
        self::$shutdownWasCalled = false;
        $this->mockCommandQueue = $this->createMock(CommandQueue::class);
    }

    public function testImplementsCommandHandlerInterface()
    {
        $message = (new ShutdownWorkerCommand('*'))->toMessage();
        $this->assertInstanceOf(CommandHandler::class, $this->createHandler($message));
    }

    public function testRetriesCommandIfMessagePidValueDoesNotMatchWithIncrementedRetryCount()
    {
        $sourceCommand = new ShutdownWorkerCommand(strval(getmypid() - 1), 42);
        $this->mockCommandQueue->expects($this->once())->method('add')
            ->willReturnCallback(function(ShutdownWorkerCommand $retryCommand) use ($sourceCommand) {
                $this->assertSame($sourceCommand->getRetryCount() + 1, $retryCommand->getRetryCount());
            });
        $this->createHandler($sourceCommand->toMessage())->process();
    }

    public function testRetriesCommandUpToMaxRetryBoundary()
    {
        $this->mockCommandQueue->expects($this->once())->method('add');
        $retry = new ShutdownWorkerCommand(strval(getmypid() - 1), ShutdownWorkerCommandHandler::MAX_RETRIES - 1);
        $this->createHandler($retry->toMessage())->process();
    }

    public function testDoesNotRetryCommandIfTheMaxRetryBoundaryIsReached()
    {
        $this->mockCommandQueue->expects($this->never())->method('add');
        $retry = new ShutdownWorkerCommand(strval(getmypid() - 1), ShutdownWorkerCommandHandler::MAX_RETRIES);
        $this->createHandler($retry->toMessage())->process();
    }

    public function testDoesNotCallExitIfMessagePidDoesNotMatchCurrentPid()
    {
        $command = new ShutdownWorkerCommand(strval(getmypid() - 1));
        $this->createHandler($command->toMessage())->process();
        $this->assertFalse(self::$shutdownWasCalled, "The shutdown() function was unexpectedly called");
    }

    public function testCallsExitIfNumericMessagePidMatchesCurrentPid()
    {
        $command = new ShutdownWorkerCommand((string) getmypid());
        $this->createHandler($command->toMessage())->process();
        $this->assertTrue(self::$shutdownWasCalled, "The shutdown() function was not called");
    }

    public function testCallsExitForWildcardPidInMessage()
    {
        $command = new ShutdownWorkerCommand('*');
        $this->createHandler($command->toMessage())->process();
        $this->assertTrue(self::$shutdownWasCalled, "The shutdown() function was not called");
    }
}

function shutdown(int $exitCode = null)
{
    ShutdownWorkerCommandHandlerTest::$shutdownWasCalled = true;
}
