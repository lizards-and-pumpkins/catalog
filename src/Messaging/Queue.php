<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface Queue extends \Countable
{
    public function count() : int;

    /**
     * @param Message $message
     * @return void
     */
    public function add(Message $message);

    public function consume(MessageReceiver $messageReceiver, int $maxNumberOfMessagesToConsume);
}
