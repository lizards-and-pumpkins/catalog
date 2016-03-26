<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;

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
     * @var ImageWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageWasAddedDomainEvent;

    /**
     * @var ImageProcessorCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageProcessorCollection;

    protected function setUp()
    {
        $this->mockImageWasAddedDomainEvent = $this->getMock(ImageWasAddedDomainEvent::class, [], [], '', false);
        $this->mockImageProcessorCollection = $this->getMock(ImageProcessorCollection::class, [], [], '', false);

        $this->handler = new ImageWasAddedDomainEventHandler(
            $this->mockImageWasAddedDomainEvent,
            $this->mockImageProcessorCollection
        );
    }

    public function testImageDomainEventHandlerIsReturned()
    {
        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $this->handler);
    }

    public function testAllImagesArePassedThroughImageProcessor()
    {
        $imageFilename = 'test_image.jpg';
        $this->mockImageWasAddedDomainEvent->method('getImageFileName')->willReturn($imageFilename);

        $this->mockImageProcessorCollection->expects($this->once())->method('process');

        $this->handler->process();
    }
}
