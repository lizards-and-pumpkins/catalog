<?php

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\FileContent;
use LizardsAndPumpkins\Import\FileStorage\StorageSpecificFileUri;

class ImageInStorage implements Image
{
    /**
     * @var StorageSpecificFileUri
     */
    private $fileURI;

    /**
     * @var ImageToImageStorage
     */
    private $imageStorage;

    /**
     * @var FileContent|null
     */
    private $fileContent;

    protected function __construct(
        StorageSpecificFileUri $fileURI,
        ImageToImageStorage $imageStorage,
        FileContent $fileContent = null
    ) {
        $this->fileURI = $fileURI;
        $this->imageStorage = $imageStorage;
        $this->fileContent = $fileContent;
    }

    /**
     * @param StorageSpecificFileUri $fileURI
     * @param ImageToImageStorage $imageStorage
     * @return ImageInStorage
     */
    public static function create(
        StorageSpecificFileUri $fileURI,
        ImageToImageStorage $imageStorage
    ) {
        return new self($fileURI, $imageStorage, null);
    }

    /**
     * @param StorageSpecificFileUri $fileURI
     * @param ImageToImageStorage $imageStorage
     * @param FileContent $fileContent
     * @return ImageInStorage
     */
    public static function createWithContent(
        StorageSpecificFileUri $fileURI,
        ImageToImageStorage $imageStorage,
        FileContent $fileContent
    ) {
        return new self($fileURI, $imageStorage, $fileContent);
    }

    /**
     * @param Context $context
     * @return HttpUrl
     */
    public function getUrl(Context $context)
    {
        return HttpUrl::fromString($this->imageStorage->url($this, $context));
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->imageStorage->isPresent($this);
    }
    
    /**
     * @return FileContent
     */
    public function getContent()
    {
        return null === $this->fileContent ?
            FileContent::fromString($this->imageStorage->read($this)) :
            $this->fileContent;
    }

    /**
     * @return StorageSpecificFileUri
     */
    public function getInStorageUri()
    {
        return $this->fileURI;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->fileURI;
    }
}
