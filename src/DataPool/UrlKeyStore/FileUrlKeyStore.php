<?php


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

    /**
     * @param string $storageDirectoryPath
     */
    public function __construct($storageDirectoryPath)
    {
        $this->storageDirectoryPath = $storageDirectoryPath;
    }

    public function clear()
    {
        (new LocalFilesystem())->removeDirectoryContents($this->storageDirectoryPath);
    }

    /**
     * @param string $dataVersionString
     * @param string $urlKeyString
     * @param string $contextDataString
     * @param string $urlKeyTypeString
     */
    public function addUrlKeyForVersion($dataVersionString, $urlKeyString, $contextDataString, $urlKeyTypeString)
    {
        $this->validateUrlKeyString($urlKeyString);
        $this->validateDataVersionString($dataVersionString);
        $this->validateContextDataString($contextDataString);
        $this->validateUrlKeyTypeString($urlKeyTypeString);
        $this->ensureDirectoryExists($this->storageDirectoryPath);
        $this->appendRecordToFile(
            $this->getUrlKeyStorageFilePathForVersion($dataVersionString),
            $this->formatRecordToWrite($urlKeyString, $contextDataString, $urlKeyTypeString)
        );
    }

    /**
     * @param string $filePath
     * @param string $record
     */
    private function appendRecordToFile($filePath, $record)
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
     * @return array[]
     */
    public function getForDataVersion($dataVersionString)
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
    private function readUrlKeysFromFile($filePath)
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
    public function parseRecord($record)
    {
        list($urlKey, $encodedContextData, $urlKeyType) = explode(self::FIELD_SEPARATOR, $record);
        return [$urlKey, base64_decode($encodedContextData), $urlKeyType];
    }

    /**
     * @param string $dataVersionString
     * @return string
     */
    private function getUrlKeyStorageFilePathForVersion($dataVersionString)
    {
        return $this->storageDirectoryPath . '/' . $dataVersionString;
    }

    /**
     * @param string $urlKey
     * @param string $contextData
     * @param string $urlKeyType
     * @return string
     */
    private function formatRecordToWrite($urlKey, $contextData, $urlKeyType)
    {
        return
            $urlKey . self::FIELD_SEPARATOR .
            base64_encode($contextData) . self::FIELD_SEPARATOR .
            $urlKeyType . PHP_EOL;
    }

    /**
     * @param string $directoryPath
     */
    private function ensureDirectoryExists($directoryPath)
    {
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0700, true);
        }
    }
}
