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

    /**
     * @test
     */
    public function itShouldBeAnImageDomainEventHandler()
    {
        $this->assertInstanceOf(ImageImportDomainEventHandler::class, $this->handler);
    }

    /**
     * @test
     */
    public function itShouldPassAllImagesThroughImageProcessor()
    {
        $imageFilename = 'test_image.jpg';

        $this->mockImageImportDomainEvent->expects($this->any())
            ->method('getImage')
            ->willReturn($imageFilename);

        $this->mockImageProcessorCollection->expects($this->once())
            ->method('process');

        $this->handler->process();
    }
}
