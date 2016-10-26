<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Util\Storage\Clearable;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystem;

class FileUrlKeyStore extends IntegrationTestUrlKeyStoreAbstract implements UrlKeyStore, Clearable
{
    const FIELD_SEPARATOR = ' ';

    /**
     * @var string
     */
    private $storageDirectoryPath;

    public function __construct(string $storageDirectoryPath)
    {
        $this->storageDirectoryPath = $storageDirectoryPath;
    }

    public function clear()
    {
        (new LocalFilesystem())->removeDirectoryContents($this->storageDirectoryPath);
    }

    public function addUrlKeyForVersion(
        string $dataVersionString,
        string $urlKeyString,
        string $contextDataString,
        string $urlKeyTypeString
    ) {
        $this->validateUrlKeyString($urlKeyString);
        $this->validateDataVersionString($dataVersionString);
        $this->ensureDirectoryExists($this->storageDirectoryPath);
        $this->appendRecordToFile(
            $this->getUrlKeyStorageFilePathForVersion($dataVersionString),
            $this->formatRecordToWrite($urlKeyString, $contextDataString, $urlKeyTypeString)
        );
    }

    private function appendRecordToFile(string $filePath, string $record)
    {
        $f = fopen($filePath, 'a');
        flock($f, LOCK_EX);
        fseek($f, 0, SEEK_END);
        fwrite($f, $record);
        flock($f, LOCK_UN);
        fclose($f);
    }

    /**
     * @param string $dataVersionString
     * @return string[]
     */
    public function getForDataVersion(string $dataVersionString) : array
    {
        $this->validateDataVersionString($dataVersionString);
        $urlKeyStorageFileForVersion = $this->getUrlKeyStorageFilePathForVersion($dataVersionString);
        if (!file_exists($urlKeyStorageFileForVersion)) {
            return [];
        }
        return $this->readUrlKeysFromFile($urlKeyStorageFileForVersion);
    }

    /**
     * @param string $filePath
     * @return string[]
     */
    private function readUrlKeysFromFile(string $filePath) : array
    {
        $f = fopen($filePath, 'r');
        flock($f, LOCK_SH);
        $urlKeys = file($filePath, FILE_IGNORE_NEW_LINES);
        flock($f, LOCK_UN);
        fclose($f);
        return array_map([$this, 'parseRecord'], $urlKeys);
    }

    /**
     * @param string $record
     * @return string[]
     */
    public function parseRecord(string $record) : array
    {
        list($urlKey, $encodedContextData, $urlKeyType) = explode(self::FIELD_SEPARATOR, $record);
        return [$urlKey, base64_decode($encodedContextData), $urlKeyType];
    }

    private function getUrlKeyStorageFilePathForVersion(string $dataVersionString) : string
    {
        return $this->storageDirectoryPath . '/' . $dataVersionString;
    }

    private function formatRecordToWrite(string $urlKey, string $contextData, string $urlKeyType) : string
    {
        return
            $urlKey . self::FIELD_SEPARATOR .
            base64_encode($contextData) . self::FIELD_SEPARATOR .
            $urlKeyType . PHP_EOL;
    }

    private function ensureDirectoryExists(string $directoryPath)
    {
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0700, true);
        }
    }
}
