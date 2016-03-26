<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerFailedMessage;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandHandlerFailedMessage
 */
class CommandHandlerFailedMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Exception
     */
    private $testException;

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

        $this->testException = new \Exception($this->exceptionMessage);

        $this->message = new CommandHandlerFailedMessage($stubCommand, $this->testException);
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

        $this->assertSame(['exception' => $this->testException], $result);
    }

    public function testItIncludesTheExceptionFileAndLineInTheSynopsis()
    {
        $synopsis = $this->message->getContextSynopsis();
        $this->assertContains($this->testException->getFile(), $synopsis);
        $this->assertContains((string) $this->testException->getLine(), $synopsis);
    }
}
