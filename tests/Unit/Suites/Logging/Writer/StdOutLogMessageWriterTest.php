<?php

namespace LizardsAndPumpkins\Logging\Writer;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Logging\LogMessageWriter;

/**
 * @covers LizardsAndPumpkins\Logging\Writer\StdOutLogMessageWriter
 */
class StdOutLogMessageWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StdOutLogMessageWriter
     */
    private $writer;

    protected function setUp()
    {
        $this->writer = new StdOutLogMessageWriter();
    }

    public function testItIsALogMessageWriter()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->writer);
    }

    public function testItOutputsTheLogMessage()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
        $testMessageString = 'The log message';
        $stubMessage = $this->getMock(LogMessage::class);
        $stubMessage->method('__toString')->willReturn($testMessageString);
        
        ob_start();
        $this->writer->write($stubMessage);
        $actual = ob_get_contents();
        ob_end_clean();
        
        $this->assertSame(get_class($stubMessage) . ":\t" . $testMessageString . "\n", $actual);
    }
}
