<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

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

    private function __construct(
        StorageSpecificFileUri $fileURI,
        FileToFileStorage $fileStorage,
        FileContent $fileContent = null
    ) {
        $this->fileStorage = $fileStorage;
        $this->fileURI = $fileURI;
        $this->fileContent = $fileContent;
    }

    public static function create(StorageSpecificFileUri $fileURI, FileToFileStorage $fileStorage) : FileInStorage
    {
        return new self($fileURI, $fileStorage, null);
    }

    public static function createWithContent(
        StorageSpecificFileUri $fileURI,
        FileToFileStorage $fileStorage,
        FileContent $fileContent
    ) : FileInStorage {
        return new self($fileURI, $fileStorage, $fileContent);
    }

    public function exists() : bool
    {
        return $this->fileStorage->isPresent($this);
    }

    public function __toString() : string
    {
        return (string) $this->fileURI;
    }

    public function getInStorageUri() : StorageSpecificFileUri
    {
        return $this->fileURI;
    }

    public function getContent() : FileContent
    {
        return is_null($this->fileContent) ?
            FileContent::fromString($this->fileStorage->read($this)) :
            $this->fileContent;
    }
}
