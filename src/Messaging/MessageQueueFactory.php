<?php

namespace LizardsAndPumpkins\Messaging;

interface MessageQueueFactory
{
    /**
     * @return Queue
     */
    public function createEventMessageQueue();

    /**
     * @return Queue
     */
    public function createCommandMessageQueue();
}
