<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\File;
use LizardsAndPumpkins\Import\FileStorage\FileContent;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri;

class FilesystemImageStorage implements ImageStorage, ImageToImageStorage
{
    /**
     * @var FilesystemFileStorage
     */
    private $fileStorage;

    /**
     * @var HttpUrl
     */
    private $mediaBaseUrlBuilder;

    /**
     * @var
     */
    private $mediaBaseDirectory;

    public function __construct(
        FilesystemFileStorage $fileStorage,
        MediaBaseUrlBuilder $mediaBaseUrlBuilder,
        string $mediaBaseDirectory
    ) {
        $this->fileStorage = $fileStorage;
        $this->mediaBaseUrlBuilder = $mediaBaseUrlBuilder;
        $this->mediaBaseDirectory = rtrim($mediaBaseDirectory, '/');
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @return Image|File
     */
    public function getFileReference(StorageAgnosticFileUri $identifier) : File
    {
        $file = $this->fileStorage->getFileReference($identifier);
        return ImageInStorage::create($file->getInStorageUri(), $this);
    }

    public function contains(StorageAgnosticFileUri $identifier) : bool
    {
        return $this->fileStorage->contains($identifier);
    }

    public function putContent(StorageAgnosticFileUri $identifier, FileContent $content): void
    {
        $this->fileStorage->putContent($identifier, $content);
    }

    public function getContent(StorageAgnosticFileUri $identifier) : FileContent
    {
        return $this->fileStorage->getContent($identifier);
    }

    public function getUrl(StorageAgnosticFileUri $identifier, Context $context) : HttpUrl
    {
        $image = $this->getFileReference($identifier);
        return $image->getUrl($context);
    }

    public function isPresent(File $image) : bool
    {
        return $this->fileStorage->isPresent($image);
    }

    public function read(File $image) : string
    {
        return $this->fileStorage->read($image);
    }

    public function write(File $file): void
    {
        $this->fileStorage->write($file);
    }

    public function url(Image $image, Context $context) : string
    {
        return $this->mediaBaseUrlBuilder->create($context)
               . substr((string) $image, strlen($this->mediaBaseDirectory) + 1);
    }
}
