<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Image\Exception\NoImageWasAddedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ImageWasAddedDomainEventTest extends TestCase
{
    /**
     * @var string
     */
    private $dummyImageFilePath;

    /**
     * @var ImageWasAddedDomainEvent
     */
    private $domainEvent;

    /**
     * @var DataVersion
     */
    private $testDataVersion;

    final protected function setUp(): void
    {
        $this->dummyImageFilePath = 'test_image.jpg';
        $this->testDataVersion = DataVersion::fromVersionString('foo');
        $this->domainEvent = new ImageWasAddedDomainEvent($this->dummyImageFilePath, $this->testDataVersion);
    }

    public function testDomainEventInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testPassedImageFilenameIsReturned(): void
    {
        $result = $this->domainEvent->getImageFilePath();
        $this->assertEquals($this->dummyImageFilePath, $result);
    }

    public function testItReturnsTheInjectedDataVersionInstance(): void
    {
        $this->assertSame($this->testDataVersion, $this->domainEvent->getDataVersion());
    }

    public function testReturnsMessageWithEventName(): void
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ImageWasAddedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithPayload(): void
    {
        $message = $this->domainEvent->toMessage();
        $payload = $message->getPayload();
        $this->assertSame($this->dummyImageFilePath, $payload['file_path']);
    }

    public function testReturnsMessageWithDataVersionInMetaData(): void
    {
        $message = $this->domainEvent->toMessage();
        $this->assertArrayHasKey('data_version', $message->getMetadata());
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $message = $this->domainEvent->toMessage();
        $rehydratedEvent = ImageWasAddedDomainEvent::fromMessage($message);
        $this->assertInstanceOf(ImageWasAddedDomainEvent::class, $rehydratedEvent);
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatchEventCode(): void
    {
        $this->expectException(NoImageWasAddedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "image_was_added" domain event, got "foo"');
        
        $message = Message::withCurrentTime('foo', [], []);
        ImageWasAddedDomainEvent::fromMessage($message);
    }
}
