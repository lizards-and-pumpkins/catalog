<?php

namespace LizardsAndPumpkins\Messaging;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface MessageReceiver
{
    /**
     * @param Message $message
     * @return void
     */
    public function receive(Message $message);
}
