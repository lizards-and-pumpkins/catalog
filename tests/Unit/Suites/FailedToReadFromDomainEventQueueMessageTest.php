<?php

namespace Brera;

/**
 * @covers \Brera\FailedToReadFromDomainEventQueueMessage
 */
class FailedToReadFromDomainEventQueueMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FailedToReadFromDomainEventQueueMessage
     */
    private $message;

    /**
     * @var \Exception
     */
    private $exception;

    protected function setUp()
    {
        $this->exception = new \Exception('foo');
        $this->message = new FailedToReadFromDomainEventQueueMessage($this->exception);

    }

    /**
     * @test
     */
    public function itShouldReturnLogMessage()
    {
        $result = (string) $this->message;
        $expectation = "Failed to read from domain event queue message with following exception:\n\nfoo";

        $this->assertEquals($expectation, $result);

    }

    /**
     * @test
     */
    public function itShouldReturnAnException()
    {
        $result = $this->message->getContext();

        $this->assertSame($this->exception, $result);
    }
}
