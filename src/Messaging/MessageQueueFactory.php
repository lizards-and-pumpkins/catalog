<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging;

interface MessageQueueFactory
{
    public function createEventMessageQueue() : Queue;

    public function createCommandMessageQueue() : Queue;
}
