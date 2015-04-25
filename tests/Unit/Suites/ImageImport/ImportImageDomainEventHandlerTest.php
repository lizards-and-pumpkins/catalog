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
    private $mockImageProcessConfiguration;

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
        $this->mockImageProcessConfiguration = $this->getMock(ImageProcessConfiguration::class, [], [], '', false);
        $this->mockImportImageDomainEvent = $this->getMock(ImportImageDomainEvent::class, [], [], '', false);
        $this->mockImageProcessor = $this->getMock(ImageProcessor::class, [], [], '', false);

        $this->handler = new ImportImageDomainEventHandler(
            $this->mockImageProcessConfiguration,
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
    public function itShouldCallConfigureMethodsOnImage()
    {
        $targetDirectory = sys_get_temp_dir();

        $command = $this->getImageProcessCommand(['resizeToWidth' => [200]]);
        $command2 = $this->getImageProcessCommand(['resize' => [400, 400]]);

        $configuration = [$command, $command2];
        $iterator = new \ArrayIterator($configuration);
        $images = [
            __DIR__ . '/../../../test_image.jpg',
            __DIR__ . '/../../../test_image2.jpg',
        ];
        $this->mockImportImageDomainEvent->expects($this->atLeastOnce())
            ->method('getImages')
            ->willReturn($images);

        $this->mockImageProcessConfiguration->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn($iterator);

        $this->mockImageProcessConfiguration->expects($this->atLeastOnce())
            ->method('getTargetDirectory')
            ->willReturn($targetDirectory);

        $numberOfImages = count($images);
        $regexTargetDirectoryAndFilename = "#$targetDirectory.*test_image(2?)\.jpg#";
        $this->mockImageProcessor->expects($this->exactly($numberOfImages * count($configuration)))
            ->method('saveAsFile')
            ->with($this->matchesRegularExpression($regexTargetDirectoryAndFilename));

        $this->mockImageProcessor->expects($this->exactly($numberOfImages))->method('resizeToWidth');
        $this->mockImageProcessor->expects($this->exactly($numberOfImages))->method('resize');

        $this->handler->process();
    }

    /**
     * @param $configuration
     * @return ImageProcessCommandSequence|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getImageProcessCommand($configuration)
    {
        /* @var $command \PHPUnit_Framework_MockObject_MockObject|ImageProcessCommandSequence */
        $command = $this->getMockBuilder(ImageProcessCommandSequence::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInstructions'])
            ->getMock();

        $command->expects($this->atLeastOnce())
            ->method('getInstructions')
            ->willReturn($configuration);

        return $command;
    }
}
