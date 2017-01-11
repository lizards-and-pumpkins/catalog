<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\MessageEnvelope;

interface DomainEvent extends MessageEnvelope
{
    
}
