<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Utils\Clearable;
use LizardsAndPumpkins\Utils\LocalFilesystem;

class FileUrlKeyStore extends IntegrationTestUrlKeyStoreAbstract implements UrlKeyStore, Clearable
{
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
     * @param string $urlKeyString
     * @param string $dataVersionString
     */
    public function addUrlKeyForVersion($urlKeyString, $dataVersionString)
    {
        $this->validateUrlKeyString($urlKeyString);
        $this->validateDataVersionString($dataVersionString);
        $urlKeyStorageFileForVersion = $this->getUrlKeyStorageFilePathForVersion($dataVersionString);
        $this->appendUrlKeyToFile($urlKeyString, $urlKeyStorageFileForVersion);
    }

    /**
     * @param string $urlKey
     * @param string $filePath
     */
    private function appendUrlKeyToFile($urlKey, $filePath)
    {
        $f = fopen($filePath, 'a');
        flock($f, LOCK_EX);
        fseek($f, 0, SEEK_END);
        fwrite($f, $urlKey . PHP_EOL);
        flock($f, LOCK_UN);
        fclose($f);
    }

    /**
     * @param string $dataVersionString
     * @return string[]
     */
    public function getForDataVersion($dataVersionString)
    {
        $this->validateDataVersionString($dataVersionString);
        $urlKeyStorageFileForVersion = $this->getUrlKeyStorageFilePathForVersion($dataVersionString);
        if (! file_exists($urlKeyStorageFileForVersion)) {
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
        return $urlKeys;
    }

    /**
     * @param string $dataVersionString
     * @return string
     */
    private function getUrlKeyStorageFilePathForVersion($dataVersionString)
    {
        return $this->storageDirectoryPath . '/' . $dataVersionString;
    }
}
