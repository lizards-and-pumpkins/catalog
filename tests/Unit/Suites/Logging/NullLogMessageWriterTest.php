<?php


namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Logging\LogMessageWriter;
use LizardsAndPumpkins\Logging\NullLogMessageWriter;

/**
 * @covers LizardsAndPumpkins\Logging\NullLogMessageWriter
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
