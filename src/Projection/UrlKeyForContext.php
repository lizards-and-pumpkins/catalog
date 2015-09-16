<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\UrlKey;

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

    public function __construct(UrlKey $urlKey, Context $context)
    {
        $this->urlKey = $urlKey;
        $this->context = $context;
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
    public function getContextAsString()
    {
        return $this->context->toString();
    }
}
