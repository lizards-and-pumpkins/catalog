<?php

namespace Brera\Product;

use Brera\AbstractHttpRequestHandler;

class ProductDetailViewRequestHandler extends AbstractHttpRequestHandler
{
    /**
     * @return string
     */
    protected final function getPageMetaInfoSnippetKey()
    {
        return $this->urlPathKeyGenerator->getUrlKeyForUrlInContext($this->url, $this->context);
    }
}
