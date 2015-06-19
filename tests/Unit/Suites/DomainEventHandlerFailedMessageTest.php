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
    private $stubException;

    /**
     * @var DomainEventHandlerFailedMessage
     */
    private $message;

    protected function setUp()
    {
        $stubDomainEvent = $this->getMockBuilder(DomainEvent::class)
            ->setMockClassName('DomainEvent')
            ->getMock();

        $this->stubException = new \Exception('foo');

        $this->message = new DomainEventHandlerFailedMessage($stubDomainEvent, $this->stubException);
    }

    public function testLogMessageIsReturned()
    {
        $expectation = "Failure during processing DomainEvent domain event with following message:\n\nfoo";

        $this->assertEquals($expectation, (string) $this->message);
    }

    public function testExceptionIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->stubException], $result);
    }
}
