<?php

namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Logging\LogMessage;

/**
 * @covers \LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Command\CommandProcessedLogMessage
 */
class ProcessTimeLoggingCommandHandlerDecoratorTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->mockLogger = $this->getMock(Logger::class);
        $this->mockDecoratedCommandHandler = $this->getMock(CommandHandler::class);
        $this->handlerDecorator = new ProcessTimeLoggingCommandHandlerDecorator(
            $this->mockDecoratedCommandHandler,
            $this->mockLogger
        );
    }

    public function testItIsACommandHandler()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->handlerDecorator);
    }

    public function testItDelegatesToTheDecoratedSubjectForProcessing()
    {
        $this->mockDecoratedCommandHandler->expects($this->once())->method('process');
        $this->handlerDecorator->process();
    }

    public function testItLogsEachCallToProcess()
    {
        $this->mockLogger->expects($this->once())->method('log');
        $this->handlerDecorator->process();
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
        $this->handlerDecorator->process();
    }
}
