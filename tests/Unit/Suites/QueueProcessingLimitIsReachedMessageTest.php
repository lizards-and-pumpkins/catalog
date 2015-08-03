<?php

namespace Brera\Queue;

use Brera\LogMessage;

/**
 * @covers \Brera\Queue\QueueProcessingLimitIsReachedMessage
 */
class QueueProcessingLimitIsReachedMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyClassName = 'foo';

    /**
     * @var int
     */
    private $dummyLimit = 1000;

    /**
     * @var QueueProcessingLimitIsReachedMessage
     */
    private $message;

    protected function setUp()
    {
        $this->message = new QueueProcessingLimitIsReachedMessage($this->dummyClassName, $this->dummyLimit);
    }

    public function testLogMessageInterfaceIsImplemented()
    {
        $this->assertInstanceOf(LogMessage::class, $this->message);
    }

    public function testLogMessageIsReturned()
    {
        $expectedMessage = sprintf('%s has reached processing limit of %d.', $this->dummyClassName, $this->dummyLimit);
        $this->assertSame($expectedMessage, (string) $this->message);
    }

    public function testEmptyContextIsReturned()
    {
        $result = $this->message->getContext();
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
}
