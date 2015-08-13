<?php

namespace Brera;

use Brera\Context\ContextSource;

class TemplateWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var TemplateWasUpdatedDomainEvent
     */
    private $domainEvent;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var TemplateProjector
     */
    private $projector;

    public function __construct(
        TemplateWasUpdatedDomainEvent $domainEvent,
        ContextSource $contextSource,
        TemplateProjector $projector
    ) {
        $this->projector = $projector;
        $this->contextSource = $contextSource;
        $this->domainEvent = $domainEvent;
    }

    public function process()
    {
        $projectionSourceData = $this->domainEvent->getProjectionSourceData();
        $this->projector->project($projectionSourceData, $this->contextSource);
    }
}
