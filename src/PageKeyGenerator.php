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
    function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @todo logic needs to be implemented and overthink
     * @todo we need somehow to define which information goes into this first key
     * @todo I like the idea to only add the version and everything else can be
     * @todo done in this first snippet, so it can be just "empty" only containing one palceholder
     *
     * @param HttpUrl $url
     *
     * @return string
     */
    public function getKeyForPage(HttpUrl $url)
    {
        return $this->getKey($url);
    }

    /**
     * @param HttpUrl $url
     *
     * @return string
     */
    public function getKeyForSnippetList(HttpUrl $url)
    {
        return $this->getKey($url) . '_l';
    }

    /**
     * @param HttpUrl $url
     * @return mixed|string
     *
     */
    private function getKey(HttpUrl $url)
    {
        $key = $url->getPath() . '_' . $this->environment->getVersion();
        $key = preg_replace('#[^a-zA-Z0-9]#', '_', $key);

        return $key;
    }
}
