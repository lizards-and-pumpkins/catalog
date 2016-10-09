<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Image\Exception\NoImageWasAddedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ImageWasAddedDomainEvent implements DomainEvent
{
    const CODE = 'image_was_added';
    
    /**
     * @var string
     */
    private $imageFilePath;
    
    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(string $imageFilePath, DataVersion $dataVersion)
    {
        $this->imageFilePath = $imageFilePath;
        $this->dataVersion = $dataVersion;
    }

    public function getImageFilePath() : string
    {
        return $this->imageFilePath;
    }

    public function getDataVersion() : DataVersion
    {
        return $this->dataVersion;
    }

    public function toMessage() : Message
    {
        $payload = ['file_path' => $this->getImageFilePath()];
        return Message::withCurrentTime(self::CODE, $payload, ['data_version' => (string) $this->getDataVersion()]);
    }

    public static function fromMessage(Message $message) : ImageWasAddedDomainEvent
    {
        if ($message->getName() !== self::CODE) {
            throw new NoImageWasAddedDomainEventMessageException(
                sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName())
            );
        }
        $dataVersion = DataVersion::fromVersionString($message->getMetadata()['data_version']);
        return new self($message->getPayload()['file_path'], $dataVersion);
    }
}
