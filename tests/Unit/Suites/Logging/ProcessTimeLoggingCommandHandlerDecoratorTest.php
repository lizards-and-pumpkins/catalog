<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Command\CommandProcessedLogMessage
 */
class ProcessTimeLoggingCommandHandlerDecoratorTest extends TestCase
{
    /**
     * @var ProcessTimeLoggingCommandHandlerDecorator
     */
    private $handlerDecorator;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var CommandHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDecoratedCommandHandler;

    /**
     * @var Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dummyMessage;

    protected function setUp()
    {
        $this->mockLogger = $this->createMock(Logger::class);
        $this->mockDecoratedCommandHandler = $this->createMock(CommandHandler::class);
        $this->handlerDecorator = new ProcessTimeLoggingCommandHandlerDecorator(
            $this->mockDecoratedCommandHandler,
            $this->mockLogger
        );
        $this->dummyMessage = $this->createMock(Message::class);
    }

    public function testItIsACommandHandler()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->handlerDecorator);
    }

    public function testItDelegatesToTheDecoratedSubjectForProcessing()
    {
        $this->mockDecoratedCommandHandler->expects($this->once())->method('process');
        $this->handlerDecorator->process($this->dummyMessage);
    }

    public function testItLogsEachCallToProcess()
    {
        $this->mockLogger->expects($this->once())->method('log');
        $this->handlerDecorator->process($this->dummyMessage);
    }

    public function testTheMessageFormat()
    {
        $this->mockLogger->expects($this->once())->method('log')
            ->willReturnCallback(function (LogMessage $logMessage) {
                if (!preg_match('/^CommandHandler::process [a-z0-9_\\\]+ \d+\.\d+/i', (string)$logMessage)) {
                    $message = sprintf('%s unexpected message format, got "%s"', get_class($logMessage), $logMessage);
                    $this->fail($message);
                }
            });
        $this->handlerDecorator->process($this->dummyMessage);
    }
}
