<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ContentBlockWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ContentBlockProjector
     */
    private $projector;

    public function __construct(ContentBlockProjector $projector)
    {
        $this->projector = $projector;
    }

    public function process(Message $message)
    {
        $domainEvent = ContentBlockWasUpdatedDomainEvent::fromMessage($message);
        $this->projector->project($domainEvent->getContentBlockSource());
    }
}
