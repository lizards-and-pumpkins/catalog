<?php

namespace LizardsAndPumpkins\Utils\FileStorage;

use LizardsAndPumpkins\Utils\FileStorage\Exception\FileDoesNotExistException;
use LizardsAndPumpkins\Utils\FileStorage\Exception\FileStorageTypeMismatchException;

class FilesystemFileStorage implements FileStorage, FileToFileStorage
{
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @param string $baseDirectory
     */
    public function __construct($baseDirectory)
    {
        $this->baseDirectory = rtrim($baseDirectory, '/');
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @return File
     */
    public function getFileReference(StorageAgnosticFileUri $identifier)
    {
        $filesystemPath = $this->buildFileSystemPath($identifier);
        return FileInStorage::create(FilesystemFileUri::fromString($filesystemPath), $this);
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @return bool
     */
    public function contains(StorageAgnosticFileUri $identifier)
    {
        $file = $this->getFileReference($identifier);
        return $this->isPresent($file);
    }

    public function putContent(StorageAgnosticFileUri $identifier, FileContent $content)
    {
        $filesystemPath = $this->buildFileSystemPath($identifier);
        $file = FileInStorage::createWithContent(FilesystemFileUri::fromString($filesystemPath), $this, $content);
        $this->write($file);
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @return FileContent
     */
    public function getContent(StorageAgnosticFileUri $identifier)
    {
        if (! $this->contains($identifier)) {
            $message = sprintf('Unable to get contents of non-existing file "%s"', $identifier);
            throw new FileDoesNotExistException($message);
        }
        $file = $this->getFileReference($identifier);
        return FileContent::fromString($this->read($file));
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @return string
     */
    private function buildFileSystemPath(StorageAgnosticFileUri $identifier)
    {
        return $this->baseDirectory . '/' . $identifier;
    }

    /**
     * @param File $file
     * @return bool
     */
    public function isPresent(File $file)
    {
        $this->validateFileStorageType($file);
        return file_exists((string) $file);
    }

    /**
     * @param File $file
     * @return string
     */
    public function read(File $file)
    {
        if (! $this->isPresent($file)) {
            throw new FileDoesNotExistException(sprintf('Unable to get contents of non-existing file "%s"', $file));
        }
        return file_get_contents($file);
    }

    public function write(File $file)
    {
        $this->validateFileStorageType($file);
        if (! file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        file_put_contents($file, $file->getContent());
    }

    private function validateFileStorageType(File $file)
    {
        $fileURI = $file->getInStorageUri();
        if (!($fileURI instanceof FilesystemFileUri)) {
            throw $this->createStorageTypeMismatchException($fileURI);
        }
    }

    /**
     * @param StorageSpecificFileUri $storageSpecificFileURI
     * @return FileStorageTypeMismatchException
     */
    private function createStorageTypeMismatchException(StorageSpecificFileUri $storageSpecificFileURI)
    {
        $thisURIType = get_class($this);
        $otherURIType = get_class($storageSpecificFileURI);
        $message = sprintf('FileStorage %s not compatible with file %s', $thisURIType, $otherURIType);
        return new FileStorageTypeMismatchException($message);
    }
}
