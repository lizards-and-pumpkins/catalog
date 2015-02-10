<?php


namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpUrl;

interface UrlPathKeyGenerator
{
    /**
     * @param string $path
     * @param Environment $environment
     * @return string
     */
    public function getUrlKeyForPathInEnvironment($path, Environment $environment);

    /**
     * @param HttpUrl $url
     * @param Environment $environment
     * @return string
     */
    public function getUrlKeyForUrlInEnvironment(HttpUrl $url, Environment $environment);

    /**
     * @param string $rootSnippetKey
     * @return string
     * @todo this is not the right class, move to a better place
     */
    public function getChildSnippetListKey($rootSnippetKey);
}
