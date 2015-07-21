<?php

namespace Brera\Content;

use Brera\CommandHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Content\UpdateContentBlockCommandHandler
 * @uses   \Brera\Content\ContentBlockWasUpdatedDomainEvent
 */
class UpdateContentBlockCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateContentBlockCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommand;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var UpdateContentBlockCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $this->mockCommand = $this->getMock(UpdateContentBlockCommand::class, [], [], '', false);
        $this->mockDomainEventQueue = $this->getMock(Queue::class);
        $this->commandHandler = new UpdateContentBlockCommandHandler($this->mockCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testContentBlockWasUpdatedDomainEventIsEmitted()
    {
        $stubContentBlockId = $this->getMock(ContentBlockId::class, [], [], '', false);
        $this->mockCommand->method('getContentBlockId')->willReturn($stubContentBlockId);

        $stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $this->mockCommand->method('getContentBlockSource')->willReturn($stubContentBlockSource);

        $this->mockDomainEventQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(ContentBlockWasUpdatedDomainEvent::class));

        $this->commandHandler->process();
    }
}
