<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoUpdateContentBlockCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateContentBlockCommand implements Command
{
    const CODE = 'update_content_block';

    /**
     * @var ContentBlockSource
     */
    private $contentBlockSource;

    public function __construct(ContentBlockSource $contentBlockSource)
    {
        $this->contentBlockSource = $contentBlockSource;
    }

    public function getContentBlockSource() : ContentBlockSource
    {
        return $this->contentBlockSource;
    }

    public function toMessage() : Message
    {
        $name = self::CODE;
        $payload = ['block' => $this->contentBlockSource->serialize()];
        $metadata = [];
        return Message::withCurrentTime($name, $payload, $metadata);
    }

    public static function fromMessage(Message $message) : UpdateContentBlockCommand
    {
        if ($message->getName() !== self::CODE) {
            throw new NoUpdateContentBlockCommandMessageException(sprintf(
                'Unable to rehydrate from "%s" queue message, expected "%s"',
                $message->getName(),
                self::CODE
            ));
        }
        return new self(ContentBlockSource::rehydrate($message->getPayload()['block']));
    }
}
