<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\UrlKey;

use LizardsAndPumpkins\Context\Context;

class UrlKeyForContext
{
    /**
     * @var UrlKey
     */
    private $urlKey;
    
    /**
     * @var Context
     */
    private $context;
    
    /**
     * @var string
     */
    private $urlKeyTypeString;

    public function __construct(UrlKey $urlKey, Context $context, string $urlKeyTypeString)
    {
        $this->urlKey = $urlKey;
        $this->context = $context;
        $this->urlKeyTypeString = $urlKeyTypeString;
    }

    public function getUrlKey() : UrlKey
    {
        return $this->urlKey;
    }

    public function getContext() : Context
    {
        return $this->context;
    }

    public function __toString() : string
    {
        return (string) $this->urlKey;
    }

    public function getContextValue(string $code) : string
    {
        return $this->context->getValue($code);
    }

    public function getType() : string
    {
        return $this->urlKeyTypeString;
    }
}
