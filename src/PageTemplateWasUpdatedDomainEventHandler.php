<?php

namespace Brera;

use Brera\Context\ContextSource;

class PageTemplateWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var PageTemplateWasUpdatedDomainEvent
     */
    private $domainEvent;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var RootSnippetProjector
     */
    private $projector;

    public function __construct(
        PageTemplateWasUpdatedDomainEvent $domainEvent,
        ContextSource $contextSource,
        RootSnippetProjector $projector
    ) {
        $this->projector = $projector;
        $this->contextSource = $contextSource;
        $this->domainEvent = $domainEvent;
    }

    public function process()
    {
        $rootSnippetSourceList = $this->domainEvent->getRootSnippetSourceList();
        $this->projector->project($rootSnippetSourceList, $this->contextSource);
    }
}
