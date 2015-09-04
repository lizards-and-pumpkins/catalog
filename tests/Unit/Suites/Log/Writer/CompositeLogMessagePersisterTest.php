<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

/**
 * @covers Brera\Log\Writer\CompositeLogMessagePersister
 */
class CompositeLogMessagePersisterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeLogMessagePersister
     */
    private $persister;

    protected function setUp()
    {
        $this->persister = CompositeLogMessagePersister::fromParameterList();
    }

    public function testItIsALogMessagePersister()
    {
        $this->assertInstanceOf(LogMessagePersister::class, $this->persister);
        $this->assertInstanceOf(LogMessagePersister::class, CompositeLogMessagePersister::fromParameterList());
    }

    public function testItThrowsAnExceptionIfAnArgumentIsNoLogMessagePersister()
    {
        $this->setExpectedException(
            NoLogMessagePersisterArgumentException::class,
            'The argument has to implement LogMessagePersister, got'
        );
        CompositeLogMessagePersister::fromParameterList($this->getMock(LogMessagePersister::class), $this);
    }

    public function testItDelegatesToLogMessagPersisterComponents()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $stubLogMessage */
        $stubLogMessage = $this->getMock(LogMessage::class);
        
        $mockPersisterA = $this->getMock(LogMessagePersister::class);
        $mockPersisterA->expects($this->once())->method('persist')->with($stubLogMessage);
        
        $mockPersisterB = $this->getMock(LogMessagePersister::class);
        $mockPersisterB->expects($this->once())->method('persist')->with($stubLogMessage);

        $composite = CompositeLogMessagePersister::fromParameterList($mockPersisterA, $mockPersisterB);
        $composite->persist($stubLogMessage);
    }
}
