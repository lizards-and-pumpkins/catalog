<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Queue;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent
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

        $stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $stubContentBlockSource->method('getContentBlockId')->willReturn($stubContentBlockId);

        $this->mockCommand->method('getContentBlockSource')->willReturn($stubContentBlockSource);

        $this->mockDomainEventQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(ContentBlockWasUpdatedDomainEvent::class));

        $this->commandHandler->process();
    }
}
