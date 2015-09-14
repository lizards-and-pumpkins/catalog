<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DomainEventHandler;

class ContentBlockWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ContentBlockWasUpdatedDomainEvent
     */
    private $domainEvent;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ContentBlockProjector
     */
    private $projector;

    public function __construct(
        ContentBlockWasUpdatedDomainEvent $domainEvent,
        ContextSource $contextSource,
        ContentBlockProjector $projector
    ) {
        $this->domainEvent = $domainEvent;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $contentBlockSource = $this->domainEvent->getContentBlockSource();
        $this->projector->project($contentBlockSource, $this->contextSource);
    }
}
