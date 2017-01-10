<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class TemplateWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var Message
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
        Message $message,
        ContextSource $contextSource,
        TemplateProjectorLocator $projectorLocator
    ) {
        $this->domainEvent = TemplateWasUpdatedDomainEvent::fromMessage($message);
        $this->projectorLocator = $projectorLocator;
        $this->contextSource = $contextSource;
    }

    public function process()
    {
        $projector = $this->projectorLocator->getTemplateProjectorForCode($this->domainEvent->getTemplateId());
        $projector->project($this->domainEvent->getTemplateContent());
    }
}
