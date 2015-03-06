<?php

namespace Brera;

use Brera\Context\ContextSourceBuilder;

class RootTemplateChangedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var RootTemplateChangedDomainEvent
     */
    private $event;

    /**
     * @var RootSnippetProjector
     */
    private $projector;

    /**
     * @var ProjectionSourceData
     */
    private $projectionSourceData;

    /**
     * @var ContextSourceBuilder
     */
    private $contextSourceBuilder;

    /**
     * @param RootTemplateChangedDomainEvent $event
     * @param RootSnippetProjector $projector
     * @param ProjectionSourceData $projectionSourceData
     * @param ContextSourceBuilder $contextSourceBuilder
     */
    public function __construct(
        RootTemplateChangedDomainEvent $event,
        RootSnippetProjector $projector,
        ProjectionSourceData $projectionSourceData,
        ContextSourceBuilder $contextSourceBuilder
    ) {
        $this->projector = $projector;
        $this->contextSourceBuilder = $contextSourceBuilder;
        $this->projectionSourceData = $projectionSourceData;
        $this->event = $event;
    }

    public function process()
    {
        $contextSource = $this->contextSourceBuilder->createFromXml($this->event->getXml());

        $this->projector->project($this->projectionSourceData, $contextSource);
    }
}
