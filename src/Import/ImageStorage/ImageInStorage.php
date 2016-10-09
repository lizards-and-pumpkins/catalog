<?php

declare(strict_types=1);

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

    public static function create(
        StorageSpecificFileUri $fileURI,
        ImageToImageStorage $imageStorage
    ) : ImageInStorage {
        return new self($fileURI, $imageStorage, null);
    }

    public static function createWithContent(
        StorageSpecificFileUri $fileURI,
        ImageToImageStorage $imageStorage,
        FileContent $fileContent
    ) : ImageInStorage {
        return new self($fileURI, $imageStorage, $fileContent);
    }

    public function getUrl(Context $context) : HttpUrl
    {
        return HttpUrl::fromString($this->imageStorage->url($this, $context));
    }

    public function exists() : bool
    {
        return $this->imageStorage->isPresent($this);
    }
    
    public function getContent() : FileContent
    {
        return null === $this->fileContent ?
            FileContent::fromString($this->imageStorage->read($this)) :
            $this->fileContent;
    }

    public function getInStorageUri() : StorageSpecificFileUri
    {
        return $this->fileURI;
    }

    public function __toString() : string
    {
        return (string) $this->fileURI;
    }
}
