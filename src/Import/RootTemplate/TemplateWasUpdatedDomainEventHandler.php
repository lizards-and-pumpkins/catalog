<?php

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\RootTemplate\Exception\NoTemplateWasUpdatedDomainEventMessageException;
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
        Message $domainEvent,
        ContextSource $contextSource,
        TemplateProjectorLocator $projectorLocator
    ) {
        if ($domainEvent->getName() !== 'template_was_updated_domain_event') {
            $message = sprintf('Expected "template_was_updated" domain event, got "%s"', $domainEvent->getName());
            throw new NoTemplateWasUpdatedDomainEventMessageException($message);
        }
        $this->domainEvent = $domainEvent;
        $this->projectorLocator = $projectorLocator;
        $this->contextSource = $contextSource;
    }

    public function process()
    {
        $payload = json_decode($this->domainEvent->getPayload(), true);
        
        $projector = $this->projectorLocator->getTemplateProjectorForCode($payload['id']);
        $projector->project($payload['template']);
    }
}
