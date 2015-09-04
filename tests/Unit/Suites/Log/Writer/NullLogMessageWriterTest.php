<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

/**
 * @covers Brera\Log\Writer\NullLogMessageWriter
 */
class NullLogMessageWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NullLogMessageWriter
     */
    private $persister;

    protected function setUp()
    {
        $this->persister = new NullLogMessageWriter();
    }
    
    public function testItIsALogMessagePersister()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->persister);
    }

    public function testItTakesALogMessage()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $mockLogMessage */
        $mockLogMessage = $this->getMock(LogMessage::class);
        $mockLogMessage->expects($this->never())->method('__toString');
        $this->persister->persist($mockLogMessage);
    }
}
