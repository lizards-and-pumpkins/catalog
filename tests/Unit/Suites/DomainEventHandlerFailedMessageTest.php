<?php

namespace LizardsAndPumpkins;

/**
 * @covers \LizardsAndPumpkins\DomainEventHandlerFailedMessage
 */
class DomainEventHandlerFailedMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Exception
     */
    private $testException;

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

        $this->testException = new \Exception($this->exceptionMessage);

        $this->message = new DomainEventHandlerFailedMessage($stubDomainEvent, $this->testException);
    }

    public function testLogMessageIsReturned()
    {
        $expectation = sprintf(
            "Failure during processing DomainEvent domain event with following message:\n%s",
            $this->exceptionMessage
        );

        $this->assertEquals($expectation, (string) $this->message);
    }

    public function testExceptionContextIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->testException], $result);
    }

    public function testItIncludesTheExceptionFileAndLineInTheSynopsis()
    {
        $synopsis = $this->message->getContextSynopsis();
        $this->assertContains($this->testException->getFile(), $synopsis);
        $this->assertContains((string) $this->testException->getLine(), $synopsis);
    }
}
