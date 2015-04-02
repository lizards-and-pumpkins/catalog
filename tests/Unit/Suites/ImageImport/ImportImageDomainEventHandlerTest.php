<?php

namespace Brera\ImageImport;

use Brera\ImageProcessor\ImageProcessor;

/**
 * @covers \Brera\ImageImport\ImportImageDomainEventHandler
 */
class ImportImageDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImportImageDomainEventHandler
     */
    private $handler;

    /**
     * @var ImageProcessConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubImageProcessConfiguration;

    /**
     * @var ImportImageDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubImportImageDomainEvent;
    /**
     * @var ImageProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageProcessor;

    protected function setUp()
    {
        $this->stubImageProcessConfiguration = $this->getMockBuilder(ImageProcessConfiguration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator', 'getTargetDirectory'])
            ->getMock();
        $this->stubImportImageDomainEvent = $this->getMockBuilder(ImportImageDomainEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getImages'])
            ->getMock();
        $this->imageProcessor = $this->getMockBuilder(ImageProcessor::class)
            ->setMethods([])
            ->getMock();
        $this->handler = new ImportImageDomainEventHandler(
            $this->stubImageProcessConfiguration,
            $this->stubImportImageDomainEvent,
            $this->imageProcessor
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
    public function itShouldCallConfigureMethodsOnImage()
    {
        $targetDirectory = sys_get_temp_dir();

        $configuration = [
            array('resizeToWidth' => [200]),
            array('resize' => [400, 400])
        ];
        $iterator = new \ArrayIterator($configuration);
        $images = [
            __DIR__ . '/../../../test_image.jpg',
            __DIR__ . '/../../../test_image2.jpg',
        ];
        $this->stubImportImageDomainEvent->expects($this->atLeastOnce())->method('getImages')->willReturn($images);

        $this->stubImageProcessConfiguration
            ->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->stubImageProcessConfiguration
            ->expects($this->atLeastOnce())
            ->method('getTargetDirectory')
            ->willReturn($targetDirectory);

        $numberOfImages = count($images);
        $regexTargetDirectoryAndFilename = "#$targetDirectory.*test_image(2?)\.jpg#";
        $this->imageProcessor
            ->expects($this->exactly($numberOfImages * count($configuration)))
            ->method('saveAsFile')
            ->with($this->matchesRegularExpression($regexTargetDirectoryAndFilename));

        $this->imageProcessor->expects($this->exactly($numberOfImages))->method('resizeToWidth');
        $this->imageProcessor->expects($this->exactly($numberOfImages))->method('resize');

        $this->handler->process();
    }
}
