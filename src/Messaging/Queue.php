<?php

namespace LizardsAndPumpkins\Messaging;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface Queue extends \Countable
{
    public function count(): int;

    public function isReadyForNext(): bool;

    /**
     * @param Message $message
     * @return void
     */
    public function add(Message $message);

    public function next(): Message;
}
