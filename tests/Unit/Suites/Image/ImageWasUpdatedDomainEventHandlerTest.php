<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageWasUpdatedDomainEventHandler
 */
class ImageWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageWasUpdatedDomainEventHandler
     */
    private $handler;

    /**
     * @var ImageWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageWasUpdatedDomainEvent;

    /**
     * @var ImageProcessorCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageProcessorCollection;

    protected function setUp()
    {
        $this->mockImageWasUpdatedDomainEvent = $this->getMock(ImageWasUpdatedDomainEvent::class, [], [], '', false);
        $this->mockImageProcessorCollection = $this->getMock(ImageProcessorCollection::class, [], [], '', false);

        $this->handler = new ImageWasUpdatedDomainEventHandler(
            $this->mockImageWasUpdatedDomainEvent,
            $this->mockImageProcessorCollection
        );
    }

    public function testImageDomainEventHandlerIsReturned()
    {
        $this->assertInstanceOf(ImageWasUpdatedDomainEventHandler::class, $this->handler);
    }

    public function testAllImagesArePassedThroughImageProcessor()
    {
        $imageFilename = 'test_image.jpg';

        $this->mockImageWasUpdatedDomainEvent->method('getImage')
            ->willReturn($imageFilename);

        $this->mockImageProcessorCollection->expects($this->once())
            ->method('process');

        $this->handler->process();
    }
}
