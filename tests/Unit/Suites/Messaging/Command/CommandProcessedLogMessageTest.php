<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Logging\LogMessage;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandProcessedLogMessage
 */
class CommandProcessedLogMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandHandler;

    private function createMessageInstance(string $message) : CommandProcessedLogMessage
    {
        return new CommandProcessedLogMessage($message, $this->mockCommandHandler);
    }

    protected function setUp()
    {
        $this->mockCommandHandler = $this->createMock(CommandHandler::class);
    }

    public function testItIsALogMessage()
    {
        $this->assertInstanceOf(LogMessage::class, $this->createMessageInstance('Test Message'));
    }

    public function testItReturnsTheMessage()
    {
        $this->assertSame('Test Message', (string)$this->createMessageInstance('Test Message'));
    }

    public function testItReturnsTheLoggedCommandHandlerAsPartOfTheMessageContext()
    {
        $message = $this->createMessageInstance('foo');
        $this->assertArrayHasKey('command_handler', $message->getContext());
        $this->assertSame($this->mockCommandHandler, $message->getContext()['command_handler']);
    }

    public function testItAddsTheCommandHandlerClassToTheContextSynopsis()
    {
        $message = $this->createMessageInstance('Test Message');
        $this->assertContains(get_class($this->mockCommandHandler), $message->getContextSynopsis());
    }
}
