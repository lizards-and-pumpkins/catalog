<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\Image\ImageWasAddedDomainEvent
 */
class AddImageCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var AddImageCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        /** @var AddImageCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(AddImageCommand::class, [], [], '', false);
        $stubCommand->method('getImageFileName')->willReturn('foo.png');

        $this->mockDomainEventQueue = $this->getMock(Queue::class);

        $this->commandHandler = new AddImageCommandHandler($stubCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testImageWasAddedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(ImageWasAddedDomainEvent::class));

        $this->commandHandler->process();
    }
}
