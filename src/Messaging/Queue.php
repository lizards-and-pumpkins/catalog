<?php

namespace LizardsAndPumpkins\Messaging;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface Queue extends \Countable
{
    /**
     * @return int
     */
    public function count();

    /**
     * @param Message $message
     * @return void
     */
    public function add(Message $message);

    /**
     * @param MessageReceiver $messageReceiver
     * @param int $maxNumberOfMessagesToConsume
     */
    public function consume(MessageReceiver $messageReceiver, $maxNumberOfMessagesToConsume);
}
