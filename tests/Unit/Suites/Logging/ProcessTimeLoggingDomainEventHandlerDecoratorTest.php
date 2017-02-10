<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Event\DomainEventProcessedLogMessage
 */
class ProcessTimeLoggingDomainEventHandlerDecoratorTest extends TestCase
{
    /**
     * @var DomainEventHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDecoratedEventHandler;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var ProcessTimeLoggingDomainEventHandlerDecorator;
     */
    private $decorator;

    /**
     * @var Message
     */
    private $dummyMessage;

    protected function setUp()
    {
        $this->dummyMessage = $this->createMock(Message::class);
        $this->mockDecoratedEventHandler = $this->createMock(DomainEventHandler::class);
        $this->mockLogger = $this->createMock(Logger::class);
        $this->decorator = new ProcessTimeLoggingDomainEventHandlerDecorator(
            $this->mockDecoratedEventHandler,
            $this->mockLogger
        );
    }

    public function testItImplementsDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->decorator);
    }

    public function testItDelegatesProcessingToComponent()
    {
        $this->mockDecoratedEventHandler->expects($this->once())->method('process');
        $this->decorator->process($this->dummyMessage);
    }

    public function testItLogsEachCallToProcess()
    {
        $this->mockLogger->expects($this->once())->method('log');
        $this->decorator->process($this->dummyMessage);
    }

    public function testTheMessageFormat()
    {
        $this->mockLogger->expects($this->once())->method('log')
            ->willReturnCallback(function (LogMessage $logMessage) {
                if (!preg_match('/^DomainEventHandler::process [a-z0-9_\\\]+ \d+\.\d+/i', (string)$logMessage)) {
                    $message = sprintf('%s unexpected message format, got "%s"', get_class($logMessage), $logMessage);
                    $this->fail($message);
                }
            });
        $this->decorator->process($this->dummyMessage);
    }
}
