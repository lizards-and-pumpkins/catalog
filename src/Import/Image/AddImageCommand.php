<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\ImageFileDoesNotExistException;
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

    public function __construct(string $imageFilePath, DataVersion $dataVersion)
    {
        if (!file_exists($imageFilePath)) {
            throw new ImageFileDoesNotExistException(
                sprintf('The image file does not exist: "%s"', $imageFilePath)
            );
        }
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
        $name = self::CODE;
        $payload = ['file_path' => $this->imageFilePath, 'data_version' => (string)$this->dataVersion];
        $metadata = [];
        return Message::withCurrentTime($name, $payload, $metadata);
    }

    public static function fromMessage(Message $message): self
    {
        if ($message->getName() != self::CODE) {
            throw new NoAddImageCommandMessageException(sprintf(
                'Unable to rehydrate from "%s" queue message, expected "%s"',
                $message->getName(),
                self::CODE
            ));
        }
        $payload = $message->getPayload();
        return new self($payload['file_path'], DataVersion::fromVersionString($payload['data_version']));
    }
}
