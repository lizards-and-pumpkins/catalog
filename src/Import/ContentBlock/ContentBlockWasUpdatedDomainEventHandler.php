<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoContentBlockWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ContentBlockWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var Message
     */
    private $domainEvent;

    /**
     * @var ContentBlockProjector
     */
    private $projector;

    public function __construct(Message $domainEvent, ContentBlockProjector $projector)
    {
        if ($domainEvent->getName() !== 'content_block_was_updated_domain_event') {
            $message = sprintf('Expected "content_block_was_updated" domain event, got "%s"', $domainEvent->getName());
            throw new NoContentBlockWasUpdatedDomainEventMessageException($message);
        }
        $this->domainEvent = $domainEvent;
        $this->projector = $projector;
    }

    public function process()
    {
        $payload = json_decode($this->domainEvent->getPayload(), true);
        $contentBlockSource = ContentBlockSource::rehydrate($payload['source']);
        $this->projector->project($contentBlockSource);
    }
}
