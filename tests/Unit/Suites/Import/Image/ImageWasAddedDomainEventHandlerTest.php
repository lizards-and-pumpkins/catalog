<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\NoImageWasAddedDomainEventMessageException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler
 */
class ImageWasAddedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageWasAddedDomainEventHandler
     */
    private $handler;

    /**
     * @var ImageProcessorCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageProcessorCollection;

    protected function setUp()
    {
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubImageWasAddedDomainEvent */
        $stubImageWasAddedDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubImageWasAddedDomainEvent->method('getName')->willReturn('image_was_added_domain_event');
        $stubImageWasAddedDomainEvent->method('getPayload')->willReturn('');

        $this->mockImageProcessorCollection = $this->getMock(ImageProcessorCollection::class, [], [], '', false);

        $this->handler = new ImageWasAddedDomainEventHandler(
            $stubImageWasAddedDomainEvent,
            $this->mockImageProcessorCollection
        );
    }

    public function testThrowsExceptionIfDomainEventNameDoesNotMatch()
    {
        $this->expectException(NoImageWasAddedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "image_was_added" domain event, got "foo_bar_domain_event"');
        
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $invalidEvent */
        $invalidEvent = $this->getMock(Message::class, [], [], '', false);
        $invalidEvent->method('getName')->willReturn('foo_bar_domain_event');

        new ImageWasAddedDomainEventHandler($invalidEvent, $this->mockImageProcessorCollection);
    }

    public function testImageDomainEventHandlerIsReturned()
    {
        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $this->handler);
    }

    public function testAllImagesArePassedThroughImageProcessor()
    {
        $this->mockImageProcessorCollection->expects($this->once())->method('process');
        $this->handler->process();
    }
}
