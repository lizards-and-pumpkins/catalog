<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\NoAddImageCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Queue\Message;

class AddImageCommand implements Command
{
    const CODE = 'add_image';

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
        if (!file_exists($imageFilePath)) {
            throw new ImageFileDoesNotExistException(
                sprintf('The image file does not exist: "%s"', $imageFilePath)
            );
        }
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
        $name = self::CODE;
        $payload = json_encode(['file_path' => $this->imageFilePath, 'data_version' => (string)$this->dataVersion]);
        $metadata = [];
        return Message::withCurrentTime($name, $payload, $metadata);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        if ($message->getName() != self::CODE) {
            throw new NoAddImageCommandMessageException(sprintf(
                'Unable to rehydrate from "%s" queue message, expected "%s"',
                $message->getName(),
                self::CODE
            ));
        }
        $payload = json_decode($message->getPayload(), true);
        return new self($payload['file_path'], DataVersion::fromVersionString($payload['data_version']));
    }
}
