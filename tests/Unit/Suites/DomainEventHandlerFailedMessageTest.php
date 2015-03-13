<?php

namespace Brera;

/**
 * @covers \Brera\DomainEventHandlerFailedMessage
 */
class DomainEventHandlerFailedMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var DomainEventHandlerFailedMessage
     */
    private $message;

    protected function setUp()
    {
        $stubDomainEvent = $this->getMockBuilder(DomainEvent::class)
            ->setMockClassName('DomainEvent')
            ->getMock();

        $this->exception = new \Exception('foo');

        $this->message = new DomainEventHandlerFailedMessage($stubDomainEvent, $this->exception);
    }

    /**
     * @test
     */
    public function itShouldReturnLogMessage()
    {
        $expectation = "Failure during processing DomainEvent domain event with following message:\n\nfoo";

        $this->assertEquals($expectation, (string) $this->message);
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
