<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoContentBlockWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ContentBlockWasUpdatedDomainEvent implements DomainEvent
{
    const CODE = 'content_block_was_updated';

    /**
     * @var ContentBlockId
     */
    private $contentBlockId;

    /**
     * @var ContentBlockSource
     */
    private $contentBlockSource;

    public function __construct(ContentBlockSource $contentBlockSource)
    {
        $this->contentBlockId = $contentBlockSource->getContentBlockId();
        $this->contentBlockSource = $contentBlockSource;
    }

    public function getContentBlockSource() : ContentBlockSource
    {
        return $this->contentBlockSource;
    }

    public function toMessage() : Message
    {
        $payload = ['id' => (string)$this->contentBlockId, 'source' => $this->contentBlockSource->serialize()];
        return Message::withCurrentTime(self::CODE, $payload, []);
    }

    public static function fromMessage(Message $message) : ContentBlockWasUpdatedDomainEvent
    {
        if ($message->getName() !== self::CODE) {
            throw new NoContentBlockWasUpdatedDomainEventMessageException(
                sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName())
            );
        }

        return new static(ContentBlockSource::rehydrate($message->getPayload()['source']));
    }
}
