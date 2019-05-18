<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Storage\Clearable;

class InMemoryQueue implements Queue, Clearable
{
    /**
     * @var mixed[]
     */
    private $queue = [];

    public function count(): int
    {
        return count($this->queue);
    }

    public function add(Message $message): void
    {
        $this->queue[] = $message->serialize();
    }

    private function next(): Message
    {
        if ([] === $this->queue) {
            throw new \UnderflowException('Trying to get next message of an empty queue');
        }

        $data = array_shift($this->queue);

        return Message::rehydrate($data);
    }

    public function clear()
    {
        $this->queue = [];
    }

    public function consume(MessageReceiver $messageReceiver): void
    {
        while ($this->count() > 0) {
            $messageReceiver->receive($this->next());
        }
    }
}
