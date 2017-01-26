<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\RootTemplate\Exception\InvalidTemplateIdException;
use LizardsAndPumpkins\Import\RootTemplate\Exception\NotUpdateTemplateCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateTemplateCommand implements Command
{
    const CODE = 'update_template';
    
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
        $this->validateTemplateId($templateId);
        $this->templateId = $templateId;
        $this->templateContent = $templateContent;
        $this->dataVersion = $dataVersion;
    }

    public function toMessage(): Message
    {
        $name = self::CODE;
        $payload = ['template_id' => $this->templateId, 'template_content' => $this->templateContent];
        $metadata = ['data_version' => (string) $this->dataVersion];

        return Message::withCurrentTime($name, $payload, $metadata);
    }

    public static function fromMessage(Message $message)
    {
        if ($message->getName() !== self::CODE) {
            throw new NotUpdateTemplateCommandMessageException(
                sprintf('Invalid message code "%s", expected %s', $message->getName(), UpdateTemplateCommand::CODE)
            );
        }
        
        return new self(
            $message->getPayload()['template_id'],
            $message->getPayload()['template_content'],
            DataVersion::fromVersionString($message->getMetadata()['data_version'])
        );
    }

    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    public function getTemplateContent(): string
    {
        return $this->templateContent;
    }

    public function getDataVersion(): DataVersion
    {
        return $this->dataVersion;
    }

    private function validateTemplateId(string $templateId)
    {
        if ('' === $templateId) {
            throw new InvalidTemplateIdException('Invalid template ID: empty string');
        }
        if (preg_match('/[ \'""]/', $templateId)) {
            throw new InvalidTemplateIdException('Invalid template ID: no spaces or quotes allowed');
        }
    }
}
