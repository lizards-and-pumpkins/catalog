<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\NoCatalogWasImportedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CatalogImportWasTriggeredDomainEvent implements DomainEvent
{
    const CODE = 'catalog_import_was_triggered';

    /**
     * @var DataVersion
     */
    private $dataVersion;

    /**
     * @var string
     */
    private $catalogImportFilePath;

    public function __construct(DataVersion $dataVersion, string $catalogImportFilePath)
    {
        $this->dataVersion = $dataVersion;
        $this->catalogImportFilePath = $catalogImportFilePath;
    }

    public function toMessage(): Message
    {
        $payload = ['import_file_path' => $this->getCatalogImportFilePath()];
        $metadata = ['data_version' => (string) $this->getDataVersion()];

        return Message::withCurrentTime(self::CODE, $payload, $metadata);
    }

    public static function fromMessage(Message $message): self
    {
        if ($message->getName() !== self::CODE) {
            throw self::createNoCatalogWasImportedDomainEventMessageException($message->getName());
        }
        $dataVersion = DataVersion::fromVersionString($message->getMetadata()['data_version']);

        return new self($dataVersion, $message->getPayload()['import_file_path']);
    }

    private static function createNoCatalogWasImportedDomainEventMessageException(string $invalidName): \LogicException
    {
        $message = sprintf('Invalid domain event "%s", expected "%s"', $invalidName, self::CODE);

        return new NoCatalogWasImportedDomainEventMessageException($message);
    }

    public function getDataVersion(): DataVersion
    {
        return $this->dataVersion;
    }

    public function getCatalogImportFilePath(): string
    {
        return $this->catalogImportFilePath;
    }
}
