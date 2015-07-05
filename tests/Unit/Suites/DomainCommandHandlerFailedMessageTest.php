<?php

namespace Brera;

/**
 * @covers \Brera\DomainCommandHandlerFailedMessage
 */
class DomainCommandHandlerFailedMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Exception
     */
    private $stubException;

    /**
     * @var DomainCommandHandlerFailedMessage
     */
    private $message;

    /**
     * @var string
     */
    private $exceptionMessage = 'foo';

    protected function setUp()
    {
        $stubDomainCommand = $this->getMockBuilder(DomainCommand::class)
            ->setMockClassName('DomainCommand')
            ->getMock();

        $this->stubException = new \Exception($this->exceptionMessage);

        $this->message = new DomainCommandHandlerFailedMessage($stubDomainCommand, $this->stubException);
    }

    public function testLogMessageIsReturned()
    {
        $expectation = sprintf(
            "Failure during processing DomainCommand domain command with following message:\n\n%s",
            $this->exceptionMessage
        );

        $this->assertEquals($expectation, (string) $this->message);
    }

    public function testExceptionIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->stubException], $result);
    }
}
