<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\FileDoesNotExistException;
use LizardsAndPumpkins\Import\FileStorage\Exception\FileStorageTypeMismatchException;

class FilesystemFileStorage implements FileStorage, FileToFileStorage
{
    /**
     * @var string
     */
    private $baseDirectory;

    public function __construct(string $baseDirectory)
    {
        $this->baseDirectory = rtrim($baseDirectory, '/');
    }

    public function getFileReference(StorageAgnosticFileUri $identifier) : File
    {
        $filesystemPath = $this->buildFileSystemPath($identifier);
        return FileInStorage::create(FilesystemFileUri::fromString($filesystemPath), $this);
    }

    public function contains(StorageAgnosticFileUri $identifier) : bool
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

    public function getContent(StorageAgnosticFileUri $identifier) : FileContent
    {
        if (! $this->contains($identifier)) {
            $message = sprintf('Unable to get contents of non-existing file "%s"', $identifier);
            throw new FileDoesNotExistException($message);
        }
        $file = $this->getFileReference($identifier);
        return FileContent::fromString($this->read($file));
    }

    private function buildFileSystemPath(StorageAgnosticFileUri $identifier) : string
    {
        return $this->baseDirectory . '/' . $identifier;
    }

    public function isPresent(File $file) : bool
    {
        $this->validateFileStorageType($file);
        return file_exists((string) $file);
    }

    public function read(File $file) : string
    {
        if (! $this->isPresent($file)) {
            throw new FileDoesNotExistException(sprintf('Unable to get contents of non-existing file "%s"', $file));
        }
        return file_get_contents((string) $file);
    }

    public function write(File $file)
    {
        $this->validateFileStorageType($file);
        $filePath = (string) $file;

        if (! file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, $file->getContent());
    }

    private function validateFileStorageType(File $file)
    {
        $fileURI = $file->getInStorageUri();
        if (!($fileURI instanceof FilesystemFileUri)) {
            throw $this->createStorageTypeMismatchException($fileURI);
        }
    }

    private function createStorageTypeMismatchException(
        StorageSpecificFileUri $storageSpecificFileURI
    ) : FileStorageTypeMismatchException {
        $thisURIType = get_class($this);
        $otherURIType = get_class($storageSpecificFileURI);
        $message = sprintf('FileStorage %s not compatible with file %s', $thisURIType, $otherURIType);
        return new FileStorageTypeMismatchException($message);
    }
}
