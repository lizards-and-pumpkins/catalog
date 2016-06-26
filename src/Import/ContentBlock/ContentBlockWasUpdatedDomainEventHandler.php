<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ContentBlockWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ContentBlockWasUpdatedDomainEvent
     */
    private $domainEvent;

    /**
     * @var ContentBlockProjector
     */
    private $projector;

    public function __construct(Message $message, ContentBlockProjector $projector)
    {
        $this->domainEvent = ContentBlockWasUpdatedDomainEvent::fromMessage($message);
        $this->projector = $projector;
    }

    public function process()
    {
        $this->projector->project($this->domainEvent->getContentBlockSource());
    }
}
