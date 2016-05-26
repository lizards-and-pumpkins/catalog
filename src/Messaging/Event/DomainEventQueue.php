<?php

declare(strict_types = 1);

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

    public function addVersioned(string $name, string $payload, DataVersion $dataVersion)
    {
        $message = $this->buildDomainEventMessage($name, $payload, $this->buildMetadataArray($dataVersion));
        $this->messageQueue->add($message);
    }

    public function addNotVersioned(string $name, string $payload)
    {
        $message = $this->buildDomainEventMessage($name, $payload);
        $this->messageQueue->add($message);
    }

    private function buildDomainEventMessage(string $name, string $payload, array $metadata = [])
    {
        $normalizedName = $this->normalizeDomainEventName($name);
        return Message::withCurrentTime($normalizedName, $payload, $metadata);
    }

    private function buildMetadataArray(DataVersion $dataVersion): array
    {
        return ['data_version' => (string)$dataVersion];
    }

    private function normalizeDomainEventName($name): string
    {
        return $this->hasSuffix($name, self::$suffix) ?
            $name :
            $name . self::$suffix;
    }

    private function hasSuffix(string $name, string $suffix): bool
    {
        return substr($name, strlen($suffix) * -1) === $suffix;
    }
}
