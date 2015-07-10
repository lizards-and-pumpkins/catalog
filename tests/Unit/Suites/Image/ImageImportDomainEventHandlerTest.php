<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageImportDomainEventHandler
 */
class ImageImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageImportDomainEventHandler
     */
    private $handler;

    /**
     * @var ImageImportDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageImportDomainEvent;

    /**
     * @var ImageProcessorCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageProcessorCollection;

    protected function setUp()
    {
        $this->mockImageImportDomainEvent = $this->getMock(ImageImportDomainEvent::class, [], [], '', false);
        $this->mockImageProcessorCollection = $this->getMock(ImageProcessorCollection::class, [], [], '', false);

        $this->handler = new ImageImportDomainEventHandler(
            $this->mockImageImportDomainEvent,
            $this->mockImageProcessorCollection
        );
    }

    public function testImageDomainEventHandlerIsReturned()
    {
        $this->assertInstanceOf(ImageImportDomainEventHandler::class, $this->handler);
    }

    public function testAllImagesArePassedThroughImageProcessor()
    {
        $imageFilename = 'test_image.jpg';

        $this->mockImageImportDomainEvent->method('getImage')
            ->willReturn($imageFilename);

        $this->mockImageProcessorCollection->expects($this->once())
            ->method('process');

        $this->handler->process();
    }
}
