<?php

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Import\RootTemplate\Exception\NoTemplateWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class TemplateWasUpdatedDomainEvent implements DomainEvent
{
    const CODE = 'template_was_updated';
    
    /**
     * @var string
     */
    private $templateId;

    /**
     * @var string
     */
    private $templateContent;

    /**
     * @param string $templateId
     * @param string $templateContent
     */
    public function __construct($templateId, $templateContent)
    {
        // todo: validate template id and content are strings
        $this->templateId = $templateId;
        $this->templateContent = $templateContent;
    }

    /**
     * @return mixed
     */
    public function getTemplateContent()
    {
        return $this->templateContent;
    }

    /**
     * @return string
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @return Message
     */
    public function toMessage()
    {
        $payload = json_encode(['id' => $this->templateId, 'template' => $this->templateContent]);
        return Message::withCurrentTime(self::CODE, $payload, []);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        if ($message->getName() !== self::CODE) {
            $message = sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName());
            throw new NoTemplateWasUpdatedDomainEventMessageException($message);
        }
        $payload = json_decode($message->getPayload(), true);
        return new self($payload['id'], $payload['template']);
    }
}
