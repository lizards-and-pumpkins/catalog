<?php


namespace LizardsAndPumpkins\Import\Product\UrlKey;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKey;

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

    /**
     * @param UrlKey $urlKey
     * @param Context $context
     * @param string $urlKeyTypeString
     */
    public function __construct(UrlKey $urlKey, Context $context, $urlKeyTypeString)
    {
        $this->urlKey = $urlKey;
        $this->context = $context;
        $this->urlKeyTypeString = $urlKeyTypeString;
    }

    /**
     * @return UrlKey
     */
    public function getUrlKey()
    {
        return $this->urlKey;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->urlKey;
    }

    /**
     * @param string $code
     * @return string
     */
    public function getContextValue($code)
    {
        return $this->context->getValue($code);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->urlKeyTypeString;
    }
}
