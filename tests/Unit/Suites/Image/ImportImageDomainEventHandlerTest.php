<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImportImageDomainEventHandler
 */
class ImportImageDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImportImageDomainEventHandler
     */
    private $handler;

    /**
     * @var ImportImageDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImportImageDomainEvent;

    /**
     * @var ImageProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageProcessor;

    protected function setUp()
    {
        $this->mockImportImageDomainEvent = $this->getMock(ImportImageDomainEvent::class, [], [], '', false);
        $this->mockImageProcessor = $this->getMock(ImageProcessor::class, [], [], '', false);

        $this->handler = new ImportImageDomainEventHandler(
            $this->mockImportImageDomainEvent,
            $this->mockImageProcessor
        );
    }

    /**
     * @test
     */
    public function itShouldBeAnImageDomainEventHandler()
    {
        $this->assertInstanceOf(ImportImageDomainEventHandler::class, $this->handler);
    }

    /**
     * @test
     */
    public function itShouldPassAllImagesThroughImageProcessor()
    {
        $imageFilename = 'test_image.jpg';

        $this->mockImportImageDomainEvent->expects($this->any())
            ->method('getImage')
            ->willReturn($imageFilename);

        $this->mockImageProcessor->expects($this->once())
            ->method('process');

        $this->handler->process();
    }
}
