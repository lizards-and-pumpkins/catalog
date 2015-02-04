<?php


namespace Brera;


use Brera\Http\HttpUrl;

class PageKeyGenerator
{
    /**
     * @var Environment
     */
    private $environment;

    /**
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param HttpUrl $url
     *
     * @return string
     */
    public function getKeyForUrl(HttpUrl $url)
    {
        $key = $url->getPath() . '_' . $this->environment->getValue(VersionedEnvironment::CODE);
        $key = preg_replace('#[^a-zA-Z0-9]#', '_', $key);

        return $key;
    }

    /**
     * @param HttpUrl $url
     *
     * @return string
     */
    public function getKeyForSnippetList(HttpUrl $url)
    {
        return $this->getKeyForUrl($url) . '_l';
    }
}
