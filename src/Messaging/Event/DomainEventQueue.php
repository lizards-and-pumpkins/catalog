<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class DomainEventQueue
{
    private static $suffix = '_domain_event';

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
        $normalizedName = $this->normalizeDomainEventName($name);
        return Message::withCurrentTime($normalizedName, $payload, $metadata);
    }

    /**
     * @param DataVersion $dataVersion
     * @return string[]
     */
    private function buildMetadataArray(DataVersion $dataVersion)
    {
        return ['data_version' => (string)$dataVersion];
    }

    /**
     * @param string $name
     * @return string
     */
    private function normalizeDomainEventName($name)
    {
        return $this->hasSuffix($name, self::$suffix) ?
            $name :
            $name . self::$suffix;
    }

    /**
     * @param string $name
     * @param string $suffix
     * @return bool
     */
    private function hasSuffix($name, $suffix)
    {
        return substr($name, strlen($suffix) * -1) === $suffix;
    }
}
