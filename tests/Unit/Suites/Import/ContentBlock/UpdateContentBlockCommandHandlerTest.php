<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoUpdateContentBlockCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class UpdateContentBlockCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockMessage;

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
        $testContentBlockId = ContentBlockId::fromString('foo bar');
        $testContentBlockSource = new ContentBlockSource($testContentBlockId, '', [], []);

        $testMessage = Message::withCurrentTime(
            UpdateContentBlockCommand::CODE,
            $testContentBlockSource->serialize(),
            []
        );

        $this->mockDomainEventQueue = $this->getMock(DomainEventQueue::class, [], [], '', false);
        $this->commandHandler = new UpdateContentBlockCommandHandler($testMessage, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testContentBlockWasUpdatedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('addNotVersioned');

        $this->commandHandler->process();
    }
}
