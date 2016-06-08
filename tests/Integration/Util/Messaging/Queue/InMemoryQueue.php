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

    /**
     * @return int
     */
    public function count()
    {
        return count($this->queue);
    }

    /**
     * @return bool
     */
    private function isReadyForNext()
    {
        return $this->count() > 0;
    }

    public function add(Message $message)
    {
        $this->queue[] = $message->serialize();
    }

    /**
     * @return Message
     */
    private function next()
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
    public function consume(MessageReceiver $messageReceiver, $numberOfMessagesBeforeReturn)
    {
        while ($this->isReadyForNext() && $numberOfMessagesBeforeReturn-- > 0) {
            $messageReceiver->receive($this->next());
        }
    }
}
