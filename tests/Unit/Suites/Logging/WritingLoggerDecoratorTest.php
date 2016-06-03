<?php

namespace LizardsAndPumpkins\Logging;

/**
 * @covers LizardsAndPumpkins\Logging\WritingLoggerDecorator
 */
class WritingLoggerDecoratorTest extends \PHPUnit_Framework_TestCase
{
    private $stubLogMessage;
    
    /**
     * @var WritingLoggerDecorator
     */
    private $decorator;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wrappedLogger;

    /**
     * @var LogMessageWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockWriter;

    protected function setUp()
    {
        $this->wrappedLogger = $this->createMock(Logger::class);
        $this->stubLogMessage = $this->createMock(LogMessage::class);
        $this->mockWriter = $this->createMock(LogMessageWriter::class);
        $this->decorator = new WritingLoggerDecorator($this->wrappedLogger, $this->mockWriter);
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

    public function testItPassesLogMessagesToTheWriter()
    {
        $this->mockWriter->expects($this->once())->method('write')->with($this->stubLogMessage);
        $this->decorator->log($this->stubLogMessage);
    }
}
