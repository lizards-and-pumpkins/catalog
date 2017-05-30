<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class TemplateWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var TemplateProjectorLocator
     */
    private $projectorLocator;

    public function __construct(TemplateProjectorLocator $projectorLocator)
    {
        $this->projectorLocator = $projectorLocator;
    }

    public function process(Message $message)
    {
        $domainEvent = TemplateWasUpdatedDomainEvent::fromMessage($message);
        $projector = $this->projectorLocator->getTemplateProjectorForCode($domainEvent->getTemplateId());
        $projector->project(TemplateProjectionData::fromEvent($domainEvent));
    }
}
