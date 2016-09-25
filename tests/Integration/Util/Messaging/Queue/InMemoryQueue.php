<?php

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

    public function count() : int
    {
        return count($this->queue);
    }

    private function isReadyForNext() : bool
    {
        return $this->count() > 0;
    }

    public function add(Message $message)
    {
        $this->queue[] = $message->serialize();
    }

    private function next() : Message
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

    /**
     * @param MessageReceiver $messageReceiver
     * @param int $numberOfMessagesBeforeReturn
     */
    public function consume(MessageReceiver $messageReceiver, $numberOfMessagesBeforeReturn) // TODO: Type hint
    {
        while ($this->isReadyForNext() && $numberOfMessagesBeforeReturn-- > 0) {
            $messageReceiver->receive($this->next());
        }
    }
}
