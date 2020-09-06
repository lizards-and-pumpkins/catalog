<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
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
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(string $templateId, string $templateContent, DataVersion $dataVersion)
    {
        $this->templateId = $templateId;
        $this->templateContent = $templateContent;
        $this->dataVersion = $dataVersion;
    }

    public function getTemplateContent(): string
    {
        return $this->templateContent;
    }

    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    public function getDataVersion(): DataVersion
    {
        return $this->dataVersion;
    }

    public function toMessage(): Message
    {
        $payload = ['id' => $this->templateId, 'template' => $this->templateContent];
        $metadata = [DataVersion::VERSION_KEY => (string) $this->dataVersion];

        return Message::withCurrentTime(self::CODE, $payload, $metadata);
    }

    public static function fromMessage(Message $message): self
    {
        if ($message->getName() !== self::CODE) {
            $message = sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName());
            throw new NoTemplateWasUpdatedDomainEventMessageException($message);
        }
        $payload = $message->getPayload();
        $dataVersion = DataVersion::fromVersionString($message->getMetadata()[DataVersion::VERSION_KEY]);

        return new self($payload['id'], $payload['template'], $dataVersion);
    }
}
