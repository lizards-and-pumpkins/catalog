<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileIsNotAFileException;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Import\Exception\NotImportCatalogCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ImportCatalogCommand implements Command
{
    const CODE = 'import_catalog';

    /**
     * @var DataVersion
     */
    private $dataVersion;

    /**
     * @var string
     */
    private $catalogDataFile;

    public function __construct(DataVersion $dataVersion, string $catalogDataFile)
    {
        $this->validateImportFile($catalogDataFile);

        $this->dataVersion = $dataVersion;
        $this->catalogDataFile = $catalogDataFile;
    }

    public function toMessage(): Message
    {
        $payload = ['catalog_data_file' => $this->catalogDataFile];
        $metadata = ['data_version' => (string) $this->dataVersion];

        return Message::withCurrentTime(self::CODE, $payload, $metadata);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        self::validateMessage($message);
        $dataVersion = DataVersion::fromVersionString($message->getMetadata()['data_version']);

        return new static($dataVersion, $message->getPayload()['catalog_data_file']);
    }

    private static function validateMessage(Message $message)
    {
        if ($message->getName() !== self::CODE) {
            throw new NotImportCatalogCommandMessageException(
                sprintf('Unable to rehydrate from "%s" queue message, expected "%s"', $message->getName(), self::CODE)
            );
        }
    }

    private function validateImportFile(string $catalogDataFile)
    {
        $this->validateFileExists($catalogDataFile);
        $this->validateFileToImportIsFile($catalogDataFile);
        $this->validateFileIsReadable($catalogDataFile);
    }

    private function validateFileExists(string $catalogDataFile)
    {
        if (!file_exists($catalogDataFile)) {
            $message = sprintf('Catalog import file "%s" does not exist', $catalogDataFile);
            throw new CatalogImportFileDoesNotExistException($message);
        }
    }

    private function validateFileToImportIsFile(string $catalogDataFile)
    {
        if (! is_file($catalogDataFile)) {
            $message = sprintf('Catalog import file "%s" is not a file', $catalogDataFile);
            throw new CatalogImportFileIsNotAFileException($message);
        }
    }

    private function validateFileIsReadable(string $catalogDataFile)
    {
        if (!is_readable($catalogDataFile)) {
            $message = sprintf('Catalog import file "%s" is not readable', $catalogDataFile);
            throw new CatalogImportFileNotReadableException($message);
        }
    }

    public function getCatalogDataFile(): string
    {
        return $this->catalogDataFile;
    }

    public function getDataVersion(): DataVersion
    {
        return $this->dataVersion;
    }
}
