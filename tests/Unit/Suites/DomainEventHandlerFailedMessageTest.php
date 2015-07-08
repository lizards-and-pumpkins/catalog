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

    /**
     * @var string
     */
    private $exceptionMessage = 'foo';

    protected function setUp()
    {
        $stubDomainEvent = $this->getMockBuilder(DomainEvent::class)
            ->setMockClassName('DomainEvent')
            ->getMock();

        $this->stubException = new \Exception($this->exceptionMessage);

        $this->message = new DomainEventHandlerFailedMessage($stubDomainEvent, $this->stubException);
    }

    public function testLogMessageIsReturned()
    {
        $expectation = sprintf(
            "Failure during processing DomainEvent domain event with following message:\n\n%s",
            $this->exceptionMessage
        );

        $this->assertEquals($expectation, (string) $this->message);
    }

    public function testExceptionContextIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->stubException], $result);
    }
}
