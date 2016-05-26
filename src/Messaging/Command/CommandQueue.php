<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CommandQueue
{
    private static $suffix = '_command';

    /**
     * @var Queue
     */
    private $messageQueue;

    public function __construct(Queue $messageQueue)
    {
        $this->messageQueue = $messageQueue;
    }

    public function add(string $name, string $payload)
    {
        $message = $this->buildMessage($name, $payload);
        $this->messageQueue->add($message);
    }

    private function buildMessage(string $name, string $payload): Message
    {
        $normalizedName = $this->normalizeCommandName($name);
        $metadata = [];
        return Message::withCurrentTime($normalizedName, $payload, $metadata);
    }

    private function normalizeCommandName($name): string
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
