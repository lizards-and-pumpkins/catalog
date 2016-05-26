<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoUpdateContentBlockCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 */
class UpdateContentBlockCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommand;

    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var UpdateContentBlockCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $this->mockCommand = $this->getMock(Message::class, [], [], '', false);
        $this->mockCommand->method('getName')->willReturn('update_content_block_command');
        $this->mockDomainEventQueue = $this->getMock(DomainEventQueue::class, [], [], '', false);
        $this->commandHandler = new UpdateContentBlockCommandHandler($this->mockCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testThrowsExceptionIfCommandNameDoesNotMatch()
    {
        $this->expectException(NoUpdateContentBlockCommandMessageException::class);
        $this->expectExceptionMessage('Expected "update_content_block" command, got "foo_command"');

        $invalidCommand = $this->getMock(Message::class, [], [], '', false);
        $invalidCommand->method('getName')->willReturn('foo_command');
        new UpdateContentBlockCommandHandler($invalidCommand, $this->mockDomainEventQueue);
    }

    public function testContentBlockWasUpdatedDomainEventIsEmitted()
    {
        $stubContentBlockId = $this->getMock(ContentBlockId::class, [], [], '', false);
        $stubContentBlockId->method('__toString')->willReturn('foo bar');

        $testContentBlockSource = new ContentBlockSource($stubContentBlockId, '', [], []);

        $this->mockCommand->method('getPayload')->willReturn($testContentBlockSource->serialize());

        $expectedPayload = ['id' => (string) $stubContentBlockId, 'source' => $testContentBlockSource->serialize()];
        $this->mockDomainEventQueue->expects($this->once())
            ->method('addNotVersioned')
            ->with('content_block_was_updated', json_encode($expectedPayload));

        $this->commandHandler->process();
    }
}
