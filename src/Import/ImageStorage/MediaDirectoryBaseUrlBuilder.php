<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\ImageStorage\Exception\InvalidMediaBaseUrlPathException;

class MediaDirectoryBaseUrlBuilder implements MediaBaseUrlBuilder
{
    /**
     * @var BaseUrlBuilder
     */
    private $baseUrlBuilder;

    /**
     * @var string
     */
    private $mediaBaseUrlPath;

    public function __construct(BaseUrlBuilder $baseUrlBuilder, string $mediaBaseUrlPath)
    {
        if ('/' !== substr($mediaBaseUrlPath, -1)) {
            throw new InvalidMediaBaseUrlPathException('The media base URL path has to end with a training slash');
        }
        
        $this->baseUrlBuilder = $baseUrlBuilder;
        $this->mediaBaseUrlPath = $mediaBaseUrlPath;
    }

    public function create(Context $context) : string
    {
        return $this->baseUrlBuilder->create($context) . $this->mediaBaseUrlPath;
    }
}
