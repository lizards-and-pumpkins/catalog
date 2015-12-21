<?php

namespace LizardsAndPumpkins\Utils\FileStorage;

class FileInStorage implements File
{
    /**
     * @var FileToFileStorage
     */
    private $fileStorage;

    /**
     * @var StorageSpecificFileUri
     */
    private $fileURI;

    /**
     * @var FileContent|null
     */
    private $fileContent;

    protected function __construct(
        StorageSpecificFileUri $fileURI,
        FileToFileStorage $fileStorage,
        FileContent $fileContent = null
    ) {
        $this->fileStorage = $fileStorage;
        $this->fileURI = $fileURI;
        $this->fileContent = $fileContent;
    }

    /**
     * @param StorageSpecificFileUri $fileURI
     * @param FileToFileStorage $fileStorage
     * @return FileInStorage
     */
    public static function create(StorageSpecificFileUri $fileURI, FileToFileStorage $fileStorage)
    {
        return new self($fileURI, $fileStorage, null);
    }

    /**
     * @param StorageSpecificFileUri $fileURI
     * @param FileToFileStorage $fileStorage
     * @param FileContent $fileContent
     * @return FileInStorage
     */
    public static function createWithContent(
        StorageSpecificFileUri $fileURI,
        FileToFileStorage $fileStorage,
        FileContent $fileContent
    ) {
        return new self($fileURI, $fileStorage, $fileContent);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->fileStorage->isPresent($this);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->fileURI;
    }

    /**
     * @return StorageSpecificFileUri
     */
    public function getInStorageUri()
    {
        return $this->fileURI;
    }

    /**
     * @return FileContent
     */
    public function getContent()
    {
        return is_null($this->fileContent) ?
            FileContent::fromString($this->fileStorage->read($this)) :
            $this->fileContent;
    }
}
