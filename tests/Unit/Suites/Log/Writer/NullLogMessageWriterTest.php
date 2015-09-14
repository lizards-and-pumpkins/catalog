<?php


namespace LizardsAndPumpkins\Log\Writer;

use LizardsAndPumpkins\Log\LogMessage;

/**
 * @covers LizardsAndPumpkins\Log\Writer\NullLogMessageWriter
 */
class NullLogMessageWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NullLogMessageWriter
     */
    private $writer;

    protected function setUp()
    {
        $this->writer = new NullLogMessageWriter();
    }
    
    public function testItIsALogMessageWriter()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->writer);
    }

    public function testItTakesALogMessage()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $mockLogMessage */
        $mockLogMessage = $this->getMock(LogMessage::class);
        $mockLogMessage->expects($this->never())->method('__toString');
        $this->writer->write($mockLogMessage);
    }
}
