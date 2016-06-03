<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\NoImageWasAddedDomainEventMessageException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
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
        $message = Message::withCurrentTime(ImageWasAddedDomainEvent::CODE, '', ['data_version' => 'foo']);

        $this->mockImageProcessorCollection = $this->getMock(ImageProcessorCollection::class, [], [], '', false);

        $this->handler = new ImageWasAddedDomainEventHandler(
            $message,
            $this->mockImageProcessorCollection
        );
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
