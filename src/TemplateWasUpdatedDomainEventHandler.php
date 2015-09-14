<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextSource;

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
     * @var TemplateProjectorLocator
     */
    private $projectorLocator;

    public function __construct(
        TemplateWasUpdatedDomainEvent $domainEvent,
        ContextSource $contextSource,
        TemplateProjectorLocator $projectorLocator
    ) {
        $this->projectorLocator = $projectorLocator;
        $this->contextSource = $contextSource;
        $this->domainEvent = $domainEvent;
    }

    public function process()
    {
        $projectionSourceData = $this->domainEvent->getProjectionSourceData();
        $projector = $this->projectorLocator->getTemplateProjectorForCode($this->domainEvent->getTemplateId());
        $projector->project($projectionSourceData, $this->contextSource);
    }
}
