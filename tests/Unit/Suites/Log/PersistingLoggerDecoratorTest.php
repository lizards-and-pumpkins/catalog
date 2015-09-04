<?php


namespace Brera\Log;

use Brera\Log\Writer\LogMessageWriter;

/**
 * @covers Brera\Log\PersistingLoggerDecorator
 */
class PersistingLoggerDecoratorTest extends \PHPUnit_Framework_TestCase
{
    private $stubLogMessage;
    
    /**
     * @var PersistingLoggerDecorator
     */
    private $decorator;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wrappedLogger;

    /**
     * @var LogMessageWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPersister;

    protected function setUp()
    {
        $this->wrappedLogger = $this->getMock(Logger::class);
        $this->stubLogMessage = $this->getMock(LogMessage::class);
        $this->mockPersister = $this->getMock(LogMessageWriter::class);
        $this->decorator = new PersistingLoggerDecorator($this->wrappedLogger, $this->mockPersister);
    }

    public function testItIsALogger()
    {
        $this->assertInstanceOf(Logger::class, $this->decorator);
    }

    public function testItDelegatesLogCallsToTheDecoratedComponent()
    {
        $this->wrappedLogger->expects($this->once())->method('log')->with($this->stubLogMessage);
        
        $this->decorator->log($this->stubLogMessage);
    }

    public function testItDelegatesGetMessagesCallsToTheDecoratedComponent()
    {
        $expected = [$this->stubLogMessage];
        $this->wrappedLogger->expects($this->once())->method('getMessages')->willReturn($expected);

        $this->assertSame($expected, $this->decorator->getMessages());
    }

    public function testItPassesLogMessagesToThePersister()
    {
        $this->mockPersister->expects($this->once())->method('persist')->with($this->stubLogMessage);
        $this->decorator->log($this->stubLogMessage);
    }
}
