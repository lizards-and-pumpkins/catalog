<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;

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

    public function __construct(ContentBlockWasUpdatedDomainEvent $domainEvent, ContentBlockProjector $projector)
    {
        $this->domainEvent = $domainEvent;
        $this->projector = $projector;
    }

    public function process()
    {
        $contentBlockSource = $this->domainEvent->getContentBlockSource();
        $this->projector->project($contentBlockSource);
    }
}
