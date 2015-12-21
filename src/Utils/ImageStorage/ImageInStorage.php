<?php

namespace LizardsAndPumpkins\Utils\ImageStorage;

use LizardsAndPumpkins\BaseUrl;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Utils\FileStorage\FileContent;
use LizardsAndPumpkins\Utils\FileStorage\StorageSpecificFileUri;

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
     * @return HttpUrl
     */
    public function getUrl()
    {
        return HttpUrl::fromString($this->imageStorage->url($this));
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
