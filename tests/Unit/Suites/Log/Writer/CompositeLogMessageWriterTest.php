<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

/**
 * @covers Brera\Log\Writer\CompositeLogMessageWriter
 */
class CompositeLogMessageWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeLogMessageWriter
     */
    private $writer;

    protected function setUp()
    {
        $this->writer = CompositeLogMessageWriter::fromParameterList();
    }

    public function testItIsALogMessageWriter()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->writer);
        $this->assertInstanceOf(LogMessageWriter::class, CompositeLogMessageWriter::fromParameterList());
    }

    public function testItThrowsAnExceptionIfAnArgumentIsNoLogMessageWriter()
    {
        $this->setExpectedException(
            NoLogMessageWriterArgumentException::class,
            'The argument has to implement LogMessageWriter, got'
        );
        CompositeLogMessageWriter::fromParameterList($this->getMock(LogMessageWriter::class), $this);
    }

    public function testItDelegatesToLogMessagWriterComponents()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $stubLogMessage */
        $stubLogMessage = $this->getMock(LogMessage::class);
        
        $mockWriterA = $this->getMock(LogMessageWriter::class);
        $mockWriterA->expects($this->once())->method('write')->with($stubLogMessage);
        
        $mockWriterB = $this->getMock(LogMessageWriter::class);
        $mockWriterB->expects($this->once())->method('write')->with($stubLogMessage);

        $composite = CompositeLogMessageWriter::fromParameterList($mockWriterA, $mockWriterB);
        $composite->write($stubLogMessage);
    }
}
