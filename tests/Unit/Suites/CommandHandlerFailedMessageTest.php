<?php

namespace Brera;

/**
 * @covers \Brera\CommandHandlerFailedMessage
 */
class CommandHandlerFailedMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Exception
     */
    private $stubException;

    /**
     * @var CommandHandlerFailedMessage
     */
    private $message;

    /**
     * @var string
     */
    private $exceptionMessage = 'foo';

    protected function setUp()
    {
        /** @var Command|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMockBuilder(Command::class)->setMockClassName('Command')->getMock();

        $this->stubException = new \Exception($this->exceptionMessage);

        $this->message = new CommandHandlerFailedMessage($stubCommand, $this->stubException);
    }

    public function testLogMessageIsReturned()
    {
        $expectation = sprintf(
            "Failure during processing Command command with following message:\n\n%s",
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
