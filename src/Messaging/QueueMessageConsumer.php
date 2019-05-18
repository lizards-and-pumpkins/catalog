<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging;

interface QueueMessageConsumer
{
    public function processAll(): void;
}
