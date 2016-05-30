<?php

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

    /**
     * @param string $name
     * @param string $payload
     */
    public function add($name, $payload)
    {
        $message = $this->buildMessage($name, $payload);
        $this->messageQueue->add($message);
    }

    /**
     * @param string $name
     * @param string $payload
     * @return Message
     */
    private function buildMessage($name, $payload)
    {
        $normalizedName = $this->normalizeCommandName($name);
        $metadata = [];
        return Message::withCurrentTime($normalizedName, $payload, $metadata);
    }

    /**
     * @param string $name
     * @return string
     */
    private function normalizeCommandName($name)
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
