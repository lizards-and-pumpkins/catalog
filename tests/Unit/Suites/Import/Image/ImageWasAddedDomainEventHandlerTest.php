<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ImageWasAddedDomainEventHandlerTest extends TestCase
{
    /**
     * @var ImageWasAddedDomainEventHandler
     */
    private $handler;

    /**
     * @var ImageProcessorCollection|MockObject
     */
    private $mockImageProcessorCollection;

    final protected function setUp(): void
    {
        $this->mockImageProcessorCollection = $this->createMock(ImageProcessorCollection::class);

        $this->handler = new ImageWasAddedDomainEventHandler($this->mockImageProcessorCollection);
    }

    public function testImageDomainEventHandlerIsReturned(): void
    {
        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $this->handler);
    }

    public function testAllImagesArePassedThroughImageProcessor(): void
    {
        $this->mockImageProcessorCollection->expects($this->once())->method('process');
        $testEvent = new ImageWasAddedDomainEvent(__FILE__, DataVersion::fromVersionString('foo'));
        $this->handler->process($testEvent->toMessage());
    }
}
