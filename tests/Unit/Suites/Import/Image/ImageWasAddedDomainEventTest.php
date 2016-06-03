<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Image\Exception\NoImageWasAddedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class ImageWasAddedDomainEventTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->dummyImageFilePath = 'test_image.jpg';
        $this->testDataVersion = DataVersion::fromVersionString('foo');
        $this->domainEvent = new ImageWasAddedDomainEvent($this->dummyImageFilePath, $this->testDataVersion);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testPassedImageFilenameIsReturned()
    {
        $result = $this->domainEvent->getImageFilePath();
        $this->assertEquals($this->dummyImageFilePath, $result);
    }

    public function testItReturnsTheInjectedDataVersionInstance()
    {
        $this->assertSame($this->testDataVersion, $this->domainEvent->getDataVersion());
    }

    public function testReturnsMessageWithEventName()
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ImageWasAddedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithPayload()
    {
        $message = $this->domainEvent->toMessage();
        $payload = json_decode($message->getPayload(), true);
        $this->assertSame($this->dummyImageFilePath, $payload['file_path']);
    }

    public function testReturnsMessageWithDataVersionInMetaData()
    {
        $message = $this->domainEvent->toMessage();
        $this->assertArrayHasKey('data_version', $message->getMetadata());
    }

    public function testCanBeRehydratedFromMessage()
    {
        $message = $this->domainEvent->toMessage();
        $rehydratedEvent = ImageWasAddedDomainEvent::fromMessage($message);
        $this->assertInstanceOf(ImageWasAddedDomainEvent::class, $rehydratedEvent);
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatchEventCode()
    {
        $this->expectException(NoImageWasAddedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "image_was_added" domain event, got "foo"');
        
        $message = Message::withCurrentTime('foo', '', []);
        ImageWasAddedDomainEvent::fromMessage($message);
    }
}
