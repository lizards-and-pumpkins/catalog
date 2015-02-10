<?php


namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpUrl;

class PoCUrlPathKeyGenerator implements UrlPathKeyGenerator
{
    /**
     * @param string $path
     * @param Environment $environment
     * @return string
     */
    public function getUrlKeyForPathInEnvironment($path, Environment $environment)
    {
        $key = $this->prependSlashIfMissing((string) $path) . '_' . $environment->getId();
        return preg_replace('#[^a-z0-9:_-]#i', '_', $key);
    }

    /**
     * @param string $path
     * @return string
     */
    private function prependSlashIfMissing($path)
    {
        return ('/' === $path{0} ? '' : '/') . $path;
    }
    
    /**
     * @param HttpUrl $url
     * @param Environment $environment
     * @return string
     */
    public function getUrlKeyForUrlInEnvironment(HttpUrl $url, Environment $environment)
    {
        return $this->getUrlKeyForPathInEnvironment($url->getPath(), $environment);
    }
}
