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
    private $persister;

    protected function setUp()
    {
        $this->persister = CompositeLogMessageWriter::fromParameterList();
    }

    public function testItIsALogMessagePersister()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->persister);
        $this->assertInstanceOf(LogMessageWriter::class, CompositeLogMessageWriter::fromParameterList());
    }

    public function testItThrowsAnExceptionIfAnArgumentIsNoLogMessagePersister()
    {
        $this->setExpectedException(
            NoLogMessagePersisterArgumentException::class,
            'The argument has to implement LogMessageWriter, got'
        );
        CompositeLogMessageWriter::fromParameterList($this->getMock(LogMessageWriter::class), $this);
    }

    public function testItDelegatesToLogMessagPersisterComponents()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $stubLogMessage */
        $stubLogMessage = $this->getMock(LogMessage::class);
        
        $mockPersisterA = $this->getMock(LogMessageWriter::class);
        $mockPersisterA->expects($this->once())->method('persist')->with($stubLogMessage);
        
        $mockPersisterB = $this->getMock(LogMessageWriter::class);
        $mockPersisterB->expects($this->once())->method('persist')->with($stubLogMessage);

        $composite = CompositeLogMessageWriter::fromParameterList($mockPersisterA, $mockPersisterB);
        $composite->persist($stubLogMessage);
    }
}
