<?php

namespace Brera;

use Brera\Context\ContextSource;

class RootTemplateChangedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var RootTemplateChangedDomainEvent
     */
    private $event;

    /**
     * @var RootSnippetSourceBuilder
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

    /**
     * @param RootTemplateChangedDomainEvent $event
     * @param RootSnippetSourceBuilder $rootSnippetSourceBuilder
     * @param ContextSource $contextSource
     * @param RootSnippetProjector $projector
     */
    public function __construct(
        RootTemplateChangedDomainEvent $event,
        RootSnippetSourceBuilder $rootSnippetSourceBuilder,
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
        $rootSnippetSource = $this->rootSnippetSourceBuilder->createFromXml($this->event->getLayoutHandle());

        $this->projector->project($rootSnippetSource, $this->contextSource);
    }
}
