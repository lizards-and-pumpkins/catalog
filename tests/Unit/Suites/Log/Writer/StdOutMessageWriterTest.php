<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

/**
 * @covers Brera\Log\Writer\StdOutMessageWriter
 */
class StdOutMessageWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StdOutMessageWriter
     */
    private $persister;

    protected function setUp()
    {
        $this->persister = new StdOutMessageWriter();
    }

    public function testItIsALogMessagePersister()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->persister);
    }

    public function testItOutputsTheLogMessage()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
        $testMessageString = 'The log message';
        $stubMessage = $this->getMock(LogMessage::class);
        $stubMessage->method('__toString')->willReturn($testMessageString);
        
        ob_start();
        $this->persister->persist($stubMessage);
        $actual = ob_get_contents();
        ob_end_clean();
        
        $this->assertSame(get_class($stubMessage) . ":\t" . $testMessageString . "\n", $actual);
    }
}
