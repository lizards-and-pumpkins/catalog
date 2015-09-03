<?php


namespace Brera\Log\Persister;

use Brera\Log\LogMessage;

/**
 * @covers Brera\Log\Persister\NullLogMessagePersister
 */
class NullLogMessagePersisterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NullLogMessagePersister
     */
    private $persister;

    protected function setUp()
    {
        $this->persister = new NullLogMessagePersister();
    }
    
    public function testItIsALogMessagePersister()
    {
        $this->assertInstanceOf(LogMessagePersister::class, $this->persister);
    }

    public function testItTakesALogMessage()
    {
        /** @var LogMessage|\PHPUnit_Framework_MockObject_MockObject $mockLogMessage */
        $mockLogMessage = $this->getMock(LogMessage::class);
        $mockLogMessage->expects($this->never())->method('__toString');
        $this->persister->persist($mockLogMessage);
    }
}
