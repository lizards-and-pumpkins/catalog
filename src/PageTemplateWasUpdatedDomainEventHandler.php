<?php

namespace Brera;

use Brera\Context\ContextSource;

class PageTemplateWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var PageTemplateWasUpdatedDomainEvent
     */
    private $event;

    /**
     * @var RootSnippetSourceListBuilder
     */
    private $rootSnippetSourceBuilder;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var RootSnippetProjector
     */
    private $projector;

    public function __construct(
        PageTemplateWasUpdatedDomainEvent $event,
        RootSnippetSourceListBuilder $rootSnippetSourceBuilder,
        ContextSource $contextSource,
        RootSnippetProjector $projector
    ) {
        $this->projector = $projector;
        $this->rootSnippetSourceBuilder = $rootSnippetSourceBuilder;
        $this->contextSource = $contextSource;
        $this->event = $event;
    }

    public function process()
    {
        $rootSnippetSource = $this->rootSnippetSourceBuilder->createFromXml($this->event->getXml());

        $this->projector->project($rootSnippetSource, $this->contextSource);
    }
}
