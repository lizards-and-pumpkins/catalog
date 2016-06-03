<?php

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

    /**
     * @return ContentBlockSource
     */
    public function getContentBlockSource()
    {
        return $this->contentBlockSource;
    }

    /**
     * @return Message
     */
    public function toMessage()
    {
        $payload = ['id' => (string)$this->contentBlockId, 'source' => $this->contentBlockSource->serialize()];
        return Message::withCurrentTime(self::CODE, json_encode($payload), []);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        if ($message->getName() !== self::CODE) {
            throw new NoContentBlockWasUpdatedDomainEventMessageException(
                sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName())
            );
        }
        
        $payload = json_decode($message->getPayload(), true);
        return new static(ContentBlockSource::rehydrate($payload['source']));
    }
}
