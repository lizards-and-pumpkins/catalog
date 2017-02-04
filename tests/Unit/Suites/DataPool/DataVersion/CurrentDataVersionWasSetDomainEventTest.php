<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataVersion\Exception\NotCurrentDataVersionWasSetMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class CurrentDataVersionWasSetDomainEventTest extends TestCase
{
    public function testImplementDomainEvent()
    {
        $event = new CurrentDataVersionWasSetDomainEvent(DataVersion::fromVersionString('foo'));
        $this->assertInstanceOf(DomainEvent::class, $event);
    }

    public function testReturnsTheDataVersion()
    {
        $testDataVersion = DataVersion::fromVersionString('bar');
        $currentDataVersionWasSetDomainEvent = new CurrentDataVersionWasSetDomainEvent($testDataVersion);
        $this->assertSame($testDataVersion, $currentDataVersionWasSetDomainEvent->getDataVersion());
    }

    /**
     * @dataProvider nonMatchingMessageNameProvider
     */
    public function testThrowsExceptionIfMessageNameDoesNotMatch(string $nonMatchingMessageName)
    {
        $expectedName = CurrentDataVersionWasSetDomainEvent::CODE;
        $message = sprintf('Message name "%s" does not match %s', $nonMatchingMessageName, $expectedName);
        $this->expectException(NotCurrentDataVersionWasSetMessageException::class);
        $this->expectExceptionMessage($message);

        CurrentDataVersionWasSetDomainEvent::fromMessage(Message::withCurrentTime($nonMatchingMessageName, [], []));
    }

    public function nonMatchingMessageNameProvider(): array
    {
        return [['foo'], ['bar']];
    }

    public function testReturnsMessageWithDataVersion()
    {
        $testDataVersion = DataVersion::fromVersionString('baz');
        $message = (new CurrentDataVersionWasSetDomainEvent($testDataVersion))->toMessage();
        
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals((string) $testDataVersion, $message->getMetadata()[DomainEventQueue::VERSION_KEY]);
    }

    public function testCanBeRehydratedFromMessage()
    {
        $testDataVersion = DataVersion::fromVersionString('baz');
        $sourceMessage = (new CurrentDataVersionWasSetDomainEvent($testDataVersion))->toMessage();
        $rehydratedEvent = CurrentDataVersionWasSetDomainEvent::fromMessage($sourceMessage);
        
        $this->assertInstanceOf(CurrentDataVersionWasSetDomainEvent::class, $rehydratedEvent);
        $this->assertEquals((string) $testDataVersion, $rehydratedEvent->getDataVersion());
    }
}
