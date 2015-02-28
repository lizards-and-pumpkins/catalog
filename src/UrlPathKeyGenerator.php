<?php


namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;

interface UrlPathKeyGenerator
{
    /**
     * @param string $path
     * @param Context $context
     * @return string
     */
    public function getUrlKeyForPathInContext($path, Context $context);

    /**
     * @param HttpUrl $url
     * @param Context $context
     * @return string
     */
    public function getUrlKeyForUrlInContext(HttpUrl $url, Context $context);
}
