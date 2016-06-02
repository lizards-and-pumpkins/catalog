<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class DomainEventQueue
{
    /**
     * @var Queue
     */
    private $messageQueue;

    public function __construct(Queue $messageQueue)
    {
        $this->messageQueue = $messageQueue;
    }

    /**
     * @param string $name
     * @param string $payload
     * @param DataVersion $dataVersion
     */
    public function addVersioned($name, $payload, DataVersion $dataVersion)
    {
        $message = $this->buildDomainEventMessage($name, $payload, $this->buildMetadataArray($dataVersion));
        $this->messageQueue->add($message);
    }

    /**
     * @param string $name
     * @param string $payload
     */
    public function addNotVersioned($name, $payload)
    {
        $message = $this->buildDomainEventMessage($name, $payload);
        $this->messageQueue->add($message);
    }

    /**
     * @param string $name
     * @param string $payload
     * @param string[] $metadata
     * @return Message
     */
    private function buildDomainEventMessage($name, $payload, array $metadata = [])
    {
        return Message::withCurrentTime($name . '_domain_event', $payload, $metadata);
    }

    /**
     * @param DataVersion $dataVersion
     * @return string[]
     */
    private function buildMetadataArray(DataVersion $dataVersion)
    {
        return ['data_version' => (string)$dataVersion];
    }
}
