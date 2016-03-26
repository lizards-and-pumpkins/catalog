<?php

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
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

    /**
     * @param BaseUrlBuilder $baseUrlBuilder
     * @param string $mediaBaseUrlPath
     */
    public function __construct(BaseUrlBuilder $baseUrlBuilder, $mediaBaseUrlPath)
    {
        if (! is_string($mediaBaseUrlPath)) {
            $type = $this->getVariableType($mediaBaseUrlPath);
            $message = sprintf('The media base URL path has to be a string, got "%s"', $type);
            throw new InvalidMediaBaseUrlPathException($message);
        }
        
        if ('/' !== substr($mediaBaseUrlPath, -1)) {
            throw new InvalidMediaBaseUrlPathException('The media base URL path has to end with a training slash');
        }
        
        $this->baseUrlBuilder = $baseUrlBuilder;
        $this->mediaBaseUrlPath = $mediaBaseUrlPath;
    }

    /**
     * @param Context $context
     * @return HttpUrl
     */
    public function create(Context $context)
    {
        return $this->baseUrlBuilder->create($context) . $this->mediaBaseUrlPath;
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }
}
