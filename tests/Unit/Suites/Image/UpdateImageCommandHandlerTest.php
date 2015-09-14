<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\Image\UpdateImageCommandHandler
 * @uses   \LizardsAndPumpkins\Image\ImageWasUpdatedDomainEvent
 */
class UpdateImageCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var UpdateImageCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        /** @var UpdateImageCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateImageCommand::class, [], [], '', false);
        $stubCommand->method('getImageFileName')->willReturn('foo.png');

        $this->mockDomainEventQueue = $this->getMock(Queue::class);

        $this->commandHandler = new UpdateImageCommandHandler($stubCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testImageWasUpdatedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(ImageWasUpdatedDomainEvent::class));

        $this->commandHandler->process();
    }
}
