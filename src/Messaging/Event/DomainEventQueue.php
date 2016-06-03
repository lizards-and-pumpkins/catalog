<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class DomainEventQueue
{
    const VERSION_KEY = 'data_version';
    
    /**
     * @var Queue
     */
    private $messageQueue;

    public function __construct(Queue $messageQueue)
    {
        $this->messageQueue = $messageQueue;
    }

    public function addVersioned(DomainEvent $event, DataVersion $dataVersion)
    {
        $message = $event->toMessage();
        $versionedMessage = $this->addDataVersionToMessageMetadata($dataVersion, $message);
        $this->messageQueue->add($versionedMessage);
    }

    public function addNotVersioned(DomainEvent $event)
    {
        $this->messageQueue->add($event->toMessage());
    }

    /**
     * @param DataVersion $dataVersion
     * @param Message $message
     * @return Message
     */
    private function addDataVersionToMessageMetadata(DataVersion $dataVersion, Message $message)
    {
        return Message::withGivenTime(
            $message->getName(),
            $message->getPayload(),
            array_merge($message->getMetadata(), [self::VERSION_KEY => (string)$dataVersion]),
            new \DateTimeImmutable('@' . $message->getTimestamp())
        );
    }
}
