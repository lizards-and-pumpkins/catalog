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
     * @var ImageProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageProcessor;

    protected function setUp()
    {
        $this->mockImageImportDomainEvent = $this->getMock(ImageImportDomainEvent::class, [], [], '', false);
        $this->mockImageProcessor = $this->getMock(ImageProcessor::class, [], [], '', false);

        $this->handler = new ImageImportDomainEventHandler(
            $this->mockImageImportDomainEvent,
            $this->mockImageProcessor
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

        $this->mockImageProcessor->expects($this->once())
            ->method('process');

        $this->handler->process();
    }
}
