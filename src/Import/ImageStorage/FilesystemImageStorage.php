<?php

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

    /**
     * @param FilesystemFileStorage $fileStorage
     * @param MediaBaseUrlBuilder $mediaBaseUrlBuilder
     * @param string $mediaBaseDirectory
     */
    public function __construct(
        FilesystemFileStorage $fileStorage,
        MediaBaseUrlBuilder $mediaBaseUrlBuilder,
        $mediaBaseDirectory
    ) {
        $this->fileStorage = $fileStorage;
        $this->mediaBaseUrlBuilder = $mediaBaseUrlBuilder;
        $this->mediaBaseDirectory = rtrim($mediaBaseDirectory, '/');
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @return Image
     */
    public function getFileReference(StorageAgnosticFileUri $identifier)
    {
        $file = $this->fileStorage->getFileReference($identifier);
        return ImageInStorage::create($file->getInStorageUri(), $this);
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @return bool
     */
    public function contains(StorageAgnosticFileUri $identifier)
    {
        return $this->fileStorage->contains($identifier);
    }

    public function putContent(StorageAgnosticFileUri $identifier, FileContent $content)
    {
        $this->fileStorage->putContent($identifier, $content);
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @return FileContent
     */
    public function getContent(StorageAgnosticFileUri $identifier)
    {
        return $this->fileStorage->getContent($identifier);
    }

    /**
     * @param StorageAgnosticFileUri $identifier
     * @param Context $context
     * @return HttpUrl
     */
    public function getUrl(StorageAgnosticFileUri $identifier, Context $context)
    {
        $image = $this->getFileReference($identifier);
        return $image->getUrl($context);
    }

    /**
     * @param File $image
     * @return bool
     */
    public function isPresent(File $image)
    {
        return $this->fileStorage->isPresent($image);
    }

    /**
     * @param File $image
     * @return string
     */
    public function read(File $image)
    {
        return $this->fileStorage->read($image);
    }

    public function write(File $file)
    {
        $this->fileStorage->write($file);
    }

    /**
     * @param Image $image
     * @param Context $context
     * @return string
     */
    public function url(Image $image, Context $context)
    {
        return $this->mediaBaseUrlBuilder->create($context) . substr($image, strlen($this->mediaBaseDirectory) + 1);
    }
}
