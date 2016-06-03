<?php

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

    /**
     * @param string $imageFilePath
     * @param DataVersion $dataVersion
     */
    public function __construct($imageFilePath, DataVersion $dataVersion)
    {
        $this->imageFilePath = $imageFilePath;
        $this->dataVersion = $dataVersion;
    }

    /**
     * @return string
     */
    public function getImageFilePath()
    {
        return $this->imageFilePath;
    }

    /**
     * @return DataVersion
     */
    public function getDataVersion()
    {
        return $this->dataVersion;
    }

    /**
     * @return Message
     */
    public function toMessage()
    {
        $payload = ['file_path' => $this->getImageFilePath()];
        return Message::withCurrentTime(self::CODE, $payload, ['data_version' => (string) $this->getDataVersion()]);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
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
