<?php


namespace LizardsAndPumpkins\Logging\Writer;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Logging\LogMessageWriter;
use LizardsAndPumpkins\Logging\Writer\CompositeLogMessageWriter;

/**
 * @covers LizardsAndPumpkins\Logging\Writer\CompositeLogMessageWriter
 */
class CompositeLogMessageWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeLogMessageWriter
     */
    private $writer;

    protected function setUp()
    {
        $this->writer = new CompositeLogMessageWriter();
    }

    public function testItIsALogMessageWriter()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->writer);
        $this->assertInstanceOf(LogMessageWriter::class, new CompositeLogMessageWriter());
    }

    public function testItDelegatesToLogMessagWriterComponents()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $stubLogMessage */
        $stubLogMessage = $this->getMock(LogMessage::class);
        
        $mockWriterA = $this->getMock(LogMessageWriter::class);
        $mockWriterA->expects($this->once())->method('write')->with($stubLogMessage);
        
        $mockWriterB = $this->getMock(LogMessageWriter::class);
        $mockWriterB->expects($this->once())->method('write')->with($stubLogMessage);

        $composite = new CompositeLogMessageWriter($mockWriterA, $mockWriterB);
        $composite->write($stubLogMessage);
    }
}
