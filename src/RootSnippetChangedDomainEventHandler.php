<?php

namespace Brera;

use Brera\Context\ContextSourceBuilder;

class RootSnippetChangedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var RootSnippetChangedDomainEvent
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
     * @param RootSnippetChangedDomainEvent $event
     * @param RootSnippetProjector $projector
     * @param ProjectionSourceData $projectionSourceData
     * @param ContextSourceBuilder $contextSourceBuilder
     */
    public function __construct(
        RootSnippetChangedDomainEvent $event,
        RootSnippetProjector $projector,
        ProjectionSourceData $projectionSourceData,
        ContextSourceBuilder $contextSourceBuilder
    )
    {
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
