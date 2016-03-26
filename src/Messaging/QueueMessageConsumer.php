<?php

namespace LizardsAndPumpkins\Messaging;

interface QueueMessageConsumer
{
    /**
     * @return void
     */
    public function process();
}
