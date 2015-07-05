<?php

namespace Brera;

/**
 * @covers \Brera\FailedToReadFromDomainCommandQueueMessage
 */
class FailedToReadFromDomainCommandQueueMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FailedToReadFromDomainCommandQueueMessage
     */
    private $message;

    /**
     * @var \Exception
     */
    private $stubException;

    protected function setUp()
    {
        $this->stubException = new \Exception('foo');
        $this->message = new FailedToReadFromDomainCommandQueueMessage($this->stubException);
    }

    public function testLogMessageIsReturned()
    {
        $result = (string) $this->message;
        $expectation = "Failed to read from domain command queue message with following exception:\n\nfoo";

        $this->assertEquals($expectation, $result);

    }

    public function testExceptionIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->stubException], $result);
    }
}
