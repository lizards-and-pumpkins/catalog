<?php

namespace Brera;

use Brera\Queue\Queue;

/**
 * @covers \Brera\CommandConsumer
 * @uses   \Brera\CommandHandlerFailedMessage
 * @uses   \Brera\FailedToReadFromCommandQueueMessage
 */
class CommandConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubQueue;

    /**
     * @var CommandHandlerLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLocator;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var CommandConsumer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandConsumer;

    protected function setUp()
    {
        $this->stubQueue = $this->getMock(Queue::class);
        $this->mockLocator = $this->getMock(CommandHandlerLocator::class, [], [], '', false);
        $this->mockLogger = $this->getMock(Logger::class);

        $this->commandConsumer = new CommandConsumer($this->stubQueue, $this->mockLocator, $this->mockLogger);
    }

    /**
     * @dataProvider getNumberOfCommandsInQueue
     * @param int $numberOfCommandsInQueue
     */
    public function testAllCommandsInQueueAreProcessed($numberOfCommandsInQueue)
    {
        $stubCommand = $this->getMock(Command::class);
        $this->stubQueue->method('next')->willReturn($stubCommand);
        $this->stubQueue->method('count')
            ->will(call_user_func_array([$this, 'onConsecutiveCalls'], range($numberOfCommandsInQueue, 0)));

        $mockCommandHandler = $this->getMock(CommandHandler::class);
        $this->mockLocator->expects($this->exactly($numberOfCommandsInQueue))->method('getHandlerFor')
            ->willReturn($mockCommandHandler);

        $this->commandConsumer->process();
    }

    /**
     * @return array[]
     */
    public function getNumberOfCommandsInQueue()
    {
        return [[1], [2], [3]];
    }

    public function testLogEntryIsWrittenIfLocatorIsNotFound()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->stubQueue->method('next')->willReturn($stubCommand);
        $this->stubQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindCommandHandlerException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->commandConsumer->process();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $this->stubQueue->expects($this->once())->method('next')->willThrowException(new \UnderflowException);
        $this->stubQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockLogger->expects($this->once())->method('log');

        $this->commandConsumer->process();
    }

    public function testConsumerStopsIfProcessingLimitIsReached()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->stubQueue->method('next')->willReturn($stubCommand);
        $this->stubQueue->method('count')->willReturn(1);

        $stubCommandHandler = $this->getMock(CommandHandler::class);
        $stubCommandHandler->expects($this->exactly(200))->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($stubCommandHandler);

        $this->commandConsumer->process();
    }
}
