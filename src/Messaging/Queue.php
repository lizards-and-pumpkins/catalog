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
     * @return bool
     */
    public function isReadyForNext();

    /**
     * @param Message $message
     * @return void
     */
    public function add(Message $message);

    /**
     * @return Message
     */
    public function next();
}
