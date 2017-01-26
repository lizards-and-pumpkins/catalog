<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\TemplateProjectionData;

class TemplateWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var Message
     */
    private $domainEvent;

    /**
     * @var TemplateProjectorLocator
     */
    private $projectorLocator;

    public function __construct(
        Message $message,
        TemplateProjectorLocator $projectorLocator
    ) {
        $this->domainEvent = TemplateWasUpdatedDomainEvent::fromMessage($message);
        $this->projectorLocator = $projectorLocator;
    }

    public function process()
    {
        $projector = $this->projectorLocator->getTemplateProjectorForCode($this->domainEvent->getTemplateId());
        $projector->project(TemplateProjectionData::fromEvent($this->domainEvent));
    }
}
