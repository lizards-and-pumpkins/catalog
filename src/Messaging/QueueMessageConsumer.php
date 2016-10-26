<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging;

interface QueueMessageConsumer
{
    /**
     * @return void
     */
    public function process();
}
